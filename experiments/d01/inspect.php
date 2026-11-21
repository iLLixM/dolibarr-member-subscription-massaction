<?php
/* SPDX-License-Identifier: GPL-3.0-or-later; read-only loaded-Core preflight. */
require __DIR__.'/core-lab.php';
if (($argv[1] ?? '') !== '--inspect') { exit(77); }
try {
 $d = d01Connect();
 $grants = array_map(static function ($r) { return array_values($r)[0]; }, d01Rows($d, 'SHOW GRANTS'));
 d01Assert(count($grants) === 2 && count(array_filter($grants, static function ($g) { return strpos($g, 'SELECT, INSERT, UPDATE, DELETE ON `cf03_restricted_lab_fixture`.*') !== false; })) === 1, 'Unexpected grants');
 $ps = $d->query('SELECT THREAD_ID FROM performance_schema.events_transactions_current WHERE 1=0');
 $errno = $d->db->errno; d01Assert($ps === false && $errno === 1142, 'P_S must be denied');
 $meta = d01Rows($d, 'SELECT VERSION() AS version,@@version_comment AS edition,@@autocommit AS ac,@@transaction_isolation AS session_isolation,@@completion_type AS completion_type')[0];
 $engine = d01Rows($d, "SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='probe'"); d01Assert($engine[0]['ENGINE'] === 'InnoDB', 'Non-InnoDB lab');
 $old = d01Rows($d, 'SELECT id,label,n FROM probe ORDER BY id');
 d01Assert(!array_filter($old, static function ($r) { return (int) $r['id'] >= 4000 && (int) $r['id'] < 4500; }), 'D01 ID range already occupied');
 $methods = array(); foreach (array('__construct','query','begin','commit','rollback') as $method) { $r = new ReflectionMethod($d,$method); $methods[$method] = array('class' => $r->getDeclaringClass()->getName(), 'source' => substr($r->getFileName(), strlen(DOL_DOCUMENT_ROOT)+1), 'line' => $r->getStartLine()); }
 $files = array(); foreach (array('core/db/DoliDB.class.php','core/db/mysqli.class.php','core/lib/functions.lib.php','core/class/conf.class.php','core/class/translate.class.php') as $p) { $files[$p] = hash_file('sha256',DOL_DOCUMENT_ROOT.'/'.$p); }
 $result = array('mode' => 'Read-only genuine loaded Core driver/library inspection; no main.inc.php/business bootstrap', 'dolibarr' => DOL_VERSION,'core_commit'=>'5c6c7ff6a5112413b1c27221c8d11750afad56f8', 'module_baseline'=>'6247704815cf532ec632616011133c646aa00005', 'reference_module_commit'=>trim(shell_exec('git -C /opt/private-fixture/module rev-parse HEAD')), 'php'=>PHP_VERSION,'driver'=>mysqli_get_client_info(),'server'=>$meta,'grants'=>$grants,'P_S_errno'=>$errno,'engine'=>$engine,'methods'=>$methods,'core_sha256'=>$files,'historical_rows'=>count($old),'historical_sha256'=>hash('sha256',json_encode($old)), 'trace'=>D01Native::$trace);
 d01Save($argv[2], $result); echo json_encode(array('status'=>'PASS','versions'=>array(DOL_VERSION,PHP_VERSION,$meta['version'],mysqli_get_client_info()),'historical_rows'=>count($old),'P_S_denied'=>$errno,'methods'=>$methods))."\n";
} catch (Throwable $e) { echo json_encode(array('status'=>'STOP','class'=>get_class($e),'reason'=>$e instanceof LogicException?$e->getMessage():'Runtime inspect failed','trace'=>D01Native::$trace))."\n"; exit(1); }
