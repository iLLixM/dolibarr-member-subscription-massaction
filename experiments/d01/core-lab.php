<?php
/* Copyright (C) 2026 iLLixM; SPDX-License-Identifier: GPL-3.0-or-later
 * EXPERIMENT ONLY: real loaded Core methods, fixed restricted artificial lab.
 * No main.inc.php, application conf.php, business bootstrap or product adapter.
 */
if (PHP_SAPI !== 'cli') { exit(77); }
$root = getenv('D01_CORE') ?: '/home/marcel/dolibarr-i105-v24-mysql';
if (trim(shell_exec('git -C '.escapeshellarg($root).' rev-parse HEAD')) !== '5c6c7ff6a5112413b1c27221c8d11750afad56f8') { throw new LogicException('Unpinned Core'); }
define('DOL_DOCUMENT_ROOT', $root.'/htdocs');
define('DOL_URL_ROOT', '');
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/conf.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/translate.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php';
require_once DOL_DOCUMENT_ROOT.'/version.inc.php';
$conf = new Conf();
$conf->db->character_set = 'utf8mb4';
$conf->db->dolibarr_main_db_collation = 'utf8mb4_unicode_ci';
$conf->file->dol_document_root = array('main' => DOL_DOCUMENT_ROOT);
$conf->global->SYSLOG_LEVEL = LOG_DEBUG;
$langs = new Translate('', $conf);
// Genuine Core dol_syslog is loaded; no log modules/third-party handlers enabled.
mysqli_report(MYSQLI_REPORT_OFF);
class D01Native extends mysqli
{
 public static $trace = array();
 public static $case = 'inspect';
 #[\ReturnTypeWillChange]
 public function query(string $query, int $result_mode = MYSQLI_STORE_RESULT)
 {
  if (preg_match('/\b(?:USE|CREATE|ALTER|DROP|TRUNCATE|GRANT|REVOKE|KILL|GLOBAL)\b/i', $query) || strpos($query, ';') !== false) { throw new LogicException('Unsafe experimental SQL'); }
  if (preg_match('/^(?:INSERT|UPDATE|DELETE|REPLACE)/i', trim($query))) {
   if (!preg_match('/^(?:INSERT INTO probe VALUES \((4[0-4][0-9]{2}),\x27D01-[a-zA-Z0-9-]+\x27,0\)|UPDATE probe SET n=[0-9]+ WHERE id=(4[0-4][0-9]{2}))$/D', $query)) { throw new LogicException('DML outside new D01 fixtures'); }
  }
  $start = microtime(true); $r = parent::query($query, $result_mode);
  self::$trace[] = array('case' => self::$case, 'connection' => $this->thread_id, 'sql' => $query,
   'start' => $start, 'end' => microtime(true), 'ok' => $r !== false, 'errno' => $this->errno,
   'rows' => $r instanceof mysqli_result ? $r->num_rows : $this->affected_rows);
  return $r;
 }
}
class D01Core extends DoliDBMysqli
{
 // Only connection instrumentation changes. Constructor/query/begin/commit/rollback are real Core.
 public function connect($host, $login, $passwd, $name, $port = 0) { return new D01Native($host, $login, $passwd, $name, $port); }
}
function d01Assert($ok, $reason) { if (!$ok) { throw new LogicException($reason); } }
function d01Config()
{
 $p = getenv('D01_CONFIG') ?: getenv('HOME').'/.config/dolibarr-sh01/app.json';
 d01Assert(is_file($p) && (fileperms($p) & 0077) === 0, 'Protected app config required');
 $c = json_decode(file_get_contents($p), true, 512, JSON_THROW_ON_ERROR);
 d01Assert($c['host'] === '127.0.0.1' && $c['port'] === 3307 && $c['database'] === 'msmsh01lab' && $c['user'] === 'msmsh01lab', 'Restricted lab only');
 return $c;
}
function d01Connect($proxyPort = 0)
{
 $c = d01Config(); $d = new D01Core('mysqli', $c['host'], $c['user'], $c['password'], $c['database'], $proxyPort ?: $c['port']); unset($c);
 d01Assert($d->connected && $d->database_selected, 'Restricted lab unavailable');
 $m = d01Rows($d, 'SELECT DATABASE() AS db,CURRENT_USER() AS account,VERSION() AS version,@@autocommit AS ac')[0];
 d01Assert($m['db'] === 'msmsh01lab' && $m['account'] === 'msmsh01lab@%' && $m['version'] === '8.4.13' && $m['ac'] === '1', 'Lab identity/preconditions mismatch');
 return $d;
}
function d01Rows($d, $sql)
{
 $r = $d->query($sql); d01Assert($r instanceof mysqli_result, 'Expected successful SELECT');
 $rows = $r->fetch_all(MYSQLI_ASSOC); $r->free(); return $rows;
}
function d01Q($d, $sql) { d01Assert($d->query($sql) !== false, 'Unexpected SQL error '.$d->lasterrno); }
function d01Value($d, $id, $locking = false) { return (int) d01Rows($d, 'SELECT n FROM probe WHERE id='.(int) $id.($locking ? ' FOR UPDATE' : ''))[0]['n']; }
function d01Seed($d, $id, $label) { d01Q($d, "INSERT INTO probe VALUES (".(int) $id.",'D01-".$label."',0)"); }
function d01Entry($d)
{
 d01Assert((int) $d->transaction_opened === 0, 'Nonzero Core depth');
 $handle = $d->db; $cid = $handle->thread_id; $offset = count(D01Native::$trace);
 d01Q($d, 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
 $result = $d->begin(); // Intentionally NOTHING between successful SET and Core BEGIN.
 $segment = array_slice(D01Native::$trace, $offset);
 d01Assert($result === 1 && $d->transaction_opened === 1 && $handle === $d->db && $cid === $d->db->thread_id, 'Core begin/identity mismatch');
 d01Assert(count($segment) === 2 && array_column($segment, 'sql') === array('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ', 'BEGIN') && $segment[0]['connection'] === $segment[1]['connection'], 'Intervening SQL');
 return array('begin' => $result, 'depth' => $d->transaction_opened, 'sql_indices' => array($offset, $offset + 1), 'connection' => $cid);
}
function d01Close($d) { try { $d->close(); } catch (Throwable $ignored) {} }
function d01Save($file, $data)
{
 $f = fopen($file, 'x'); d01Assert($f !== false, 'Refuse overwrite');
 fwrite($f, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n"); fclose($f);
}
