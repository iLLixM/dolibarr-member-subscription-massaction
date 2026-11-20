<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
// Two injected Core-user SQL faults through the REAL book() catch and batch
// orchestrator, using pinned Dolibarr source + a synthetic DoliDB. No HTTP/DB.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start();
require __DIR__.'/booking.php';
ob_end_clean();
require_once DOL_DOCUMENT_ROOT.'/core/modules/syslog/mod_syslog_file.php';
/** Models Core User's ignored fetch_optionals() SQL failure, without replacing the
 * pinned Core User class already loaded by the booking test bootstrap.
 */
class F29FlowUser
{
    public static $latest;
    public $db; public $id = 0; public $status = 1; public $errors = array();
    public function __construct($db) { $this->db = $db; self::$latest = $this; }
    public function fetch($id)
    {
        $this->id = $id;
        $result = $this->db->query('SELECT rowid FROM test_user WHERE rowid = '.((int) $id));
        if (!$result) { return -1; }
        $this->fetch_optionals(); // Core does not inspect its negative result.
        return 1;
    }
    public function fetch_optionals()
    {
        $result = $this->db->query('SELECT rowid FROM test_user_extrafields WHERE fk_object = '.$this->id);
        if (!$result) { $this->errors[] = 'Synthetic SQL error 1213'; return -1; }
        return 0;
    }
    public function loadRights($tag = '', $refresh = 0)
    {
        foreach (array('user_rights', 'usergroup_rights') as $table) {
            $result = $this->db->query('SELECT permission FROM test_'.$table);
            if (!$result) { continue; } // Pinned Core's silent void return.
            $this->db->free($result);
        }
    }
    public function hasRight(...$rights) { return true; }
}
class F29FlowDatabase extends BookingDatabase
{
    public $family = 'mysql'; public $profileRaw; public $fault = ''; public $userCalls = array();
    public function __construct()
    {
        parent::__construct();
        $this->profileRaw = json_encode(array('version'=>1,'family'=>'mysql',
            'profile'=>'CoreGuarded','nonce'=>str_repeat('c',32)));
    }
    public function query($sql)
    {
        if ($sql === 'SELECT CONNECTION_ID() AS cid, @@SESSION.autocommit AS ac, @@SESSION.completion_type AS completion') {
            $this->queries[] = $sql;
            return (object) array('rows'=>array((object) array('cid'=>'51','ac'=>'1','completion'=>'NO_CHAIN')));
        }
        if ($sql === 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ') {
            $this->queries[] = $sql; return true;
        }
        if (strpos($sql, 'SELECT rowid FROM test_user WHERE') === 0) {
            $this->userCalls[] = 'user-fetch';
            return (object) array('rows'=>array((object) array('rowid'=>17)));
        }
        if (strpos($sql, 'SELECT rowid FROM test_user_extrafields') === 0) {
            $this->userCalls[] = 'optionals';
            return $this->fault === 'optionals' ? false : (object) array('rows'=>array());
        }
        if (strpos($sql, 'SELECT permission FROM test_user_rights') === 0) {
            $this->userCalls[] = 'rights-user';
            return $this->fault === 'rights' ? false : (object) array('rows'=>array());
        }
        if (strpos($sql, 'SELECT permission FROM test_usergroup_rights') === 0) {
            $this->userCalls[] = 'rights-group';
            return (object) array('rows'=>array());
        }
        return parent::query($sql);
    }
}
class F29FlowCoreTransaction extends MsmCoreGuardedTransaction
{
    protected function nativeHandle() { return $this->dbForTest->native; }
    protected function nativeThread($handle) { return (string) $handle->thread_id; }
    private $dbForTest;
    public function __construct($db) { $this->dbForTest = $db; parent::__construct($db); }
}
class F29FlowBookingService extends MsmBookingService
{
    use ProfileBookingFixture;
    public $memberCalls = 0; private $tx;
    public function __construct($db)
    {
        $this->profileDb = $db;
        parent::__construct($db, new SyntheticDatabaseState($db));
        $this->tx = new F29FlowCoreTransaction($db);
    }
    protected function coreTransaction() { return $this->tx; }
    protected function newCurrentUser() { return new F29FlowUser($this->profileDb); }
    protected function member($id) { $this->memberCalls++; throw new LogicException('Business operation reached after injected fault'); }
}
class F29FlowMailer
{
    public $calls = 0;
    public function send(...$args) { $this->calls++; throw new LogicException('Mail after synthetic SQL fault'); }
}
$checks = 0;
function f29FlowCheck($ok, $label)
{
    global $checks;
    if (!$ok) { throw new RuntimeException('F29 flow FAIL: '.$label); }
    ++$checks;
}
$root = sys_get_temp_dir().'/f29-core-flow-'.bin2hex(random_bytes(12));
if (!mkdir($root, 0700)) { throw new RuntimeException('Cannot create F29 test session directory'); }
$oldPath = session_save_path(); $oldCookies = ini_get('session.use_cookies');
$oldLimiter = session_cache_limiter(); $oldConf = clone $conf;
ini_set('session.use_cookies', '0'); session_cache_limiter(''); session_save_path($root);
$conf->modules['syslog'] = 1;
$conf->modules['agenda'] = 0;
$conf->loghandlers = array(new mod_syslog_file());
$conf->global->SYSLOG_FILE = $root.'/qualified.log';
try {
    foreach (array('optionals','rights') as $fault) {
        $db = new F29FlowDatabase(); $db->fault = $fault;
        $service = new F29FlowBookingService($db);
        $caller = new PreviewUser(); $caller->id = 17;
        $binding = FixtureTransactionProfile::context($db, $caller);
        $opts = array('invoice'=>false,'autocreate'=>false,'sendmail'=>false,'description'=>'Synthetic contribution');
        $row = array('start'=>stamp('2080-01-01'),'end'=>stamp('2080-12-31'),'amount'=>120.0,
            'description'=>'Synthetic contribution','fingerprint'=>'synthetic','errors'=>array(),'overlaps'=>array());
        $key = str_repeat($fault === 'optionals' ? 'a' : 'b', 32);
        $state = array('owner'=>17,'entity'=>1,'phase'=>'confirm','revision'=>3,'ids'=>array(1,2),
            'profile'=>$binding,'options'=>$opts,'rows'=>array(1=>$row,2=>$row));
        session_id('f29flow'.bin2hex(random_bytes(12)));
        f29FlowCheck(session_start(), 'file session opened '.$fault);
        $_SESSION = array('msm_previews'=>array($key=>$state));
        $checkpoint = new MsmFileBookingCheckpoint($key);
        f29FlowCheck(session_write_close(), 'confirmation persisted '.$fault);
        f29FlowCheck(session_start(), 'reopen before confirmation consumption '.$fault);
        $processing = msmConsumeConfirmation($state, array('preview'=>$key,'revision'=>'3','action'=>'book','token'=>'synthetic'));
        $_SESSION['msm_previews'][$key] = $processing;
        f29FlowCheck(session_write_close(), 'consumption persisted '.$fault);
        $mail = new F29FlowMailer();
        $done = msmProcessBooking($processing, $service, $mail, $caller, $checkpoint);
        $first = $done['results'][1]; $second = $done['results'][2];
        f29FlowCheck($first['status'] === 'unknown' && $first['stop'] === true
            && $first['message'] === 'MSMTransactionUnknown' && $first['rollback'] === 'unconfirmed',
            'real book catch classifies '.$fault.' as UNKNOWN/STOP');
        f29FlowCheck($second['status'] === 'not_processed' && $done['batch_stop'] && $done['phase'] === 'done',
            'real batch orchestrator forbids second member '.$fault);
        f29FlowCheck(in_array('begin', $db->events, true)
            && count(array_keys($db->events, 'lock', true)) === 2
            && !in_array('commit', $db->events, true)
            && !in_array('rollback', $db->events, true)
            && !in_array('subscription', $db->events, true)
            && $db->transaction_opened === 1 && $service->memberCalls === 0 && $mail->calls === 0,
            'no module rollback, COMMIT, business call, retry or mail '.$fault);
        f29FlowCheck($db->userCalls === ($fault === 'optionals'
            ? array('user-fetch','optionals')
            : array('user-fetch','optionals','rights-user')),
            'first SQL failure cuts off the user reload '.$fault);
        f29FlowCheck(F29FlowUser::$latest->db === $db, 'original user DoliDB restored '.$fault);
        f29FlowCheck(session_start(), 'read durable terminal result '.$fault);
        $stored = $_SESSION['msm_previews'][$key];
        f29FlowCheck($stored['results'][1]['status'] === 'unknown'
            && $stored['results'][2]['status'] === 'not_processed',
            'durable checkpoint preserves SQL failure and stops batch '.$fault);
        session_destroy(); session_write_close();
    }
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) { session_abort(); }
    $conf = $oldConf;
    session_save_path($oldPath); ini_set('session.use_cookies', (string) $oldCookies);
    session_cache_limiter($oldLimiter);
    foreach (glob($root.'/*') as $file) { unlink($file); }
    rmdir($root);
}
echo 'F29-02 real book()/batch error flow: 2 scenarios / '.$checks
    .' assertions PASS (synthetic Core SQL and local sessions; no DB/HTTP).'.PHP_EOL;
