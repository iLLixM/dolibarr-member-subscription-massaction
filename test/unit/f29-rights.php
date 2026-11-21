<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
/** Two SELECTs, mirroring Core User::loadRights() including silent failures. */
class F29Database
{
    public $calls = array(); public $faultAt = ''; public $faultMode = ''; public $faultCode = 0;
    public function query($sql)
    {
        $site = strpos($sql, 'user_rights') !== false ? 'user' : 'group';
        $this->calls[] = $site;
        if ($site === $this->faultAt && $this->faultMode === 'query') { return false; }
        if ($site === $this->faultAt && $this->faultMode === 'throw') {
            throw new RuntimeException('Simulated database fault', $this->faultCode);
        }
        return (object) array('site' => $site, 'rows' => array((object) array('right' => 1)));
    }
    public function num_rows($res)
    {
        if ($res->site === $this->faultAt && $this->faultMode === 'num_rows') { return false; }
        if ($res->site === $this->faultAt && $this->faultMode === 'num_rows_negative') { return -1; }
        return count($res->rows);
    }
    public function fetch_object($res)
    {
        if ($res->site === $this->faultAt && $this->faultMode === 'fetch_object') { return false; }
        if ($res->site === $this->faultAt && $this->faultMode === 'fetch_object_null') { return null; }
        return array_shift($res->rows);
    }
    public function free($res) {}
    public function prefix() { return 'test_'; }
}
class User
{
    public static $result = 1; public static $fetchError = 0; public static $latest;
    public static $optionalSqlError = false;
    public $db; public $status = 1; public $errors = array();
    public function __construct($db) { $this->db = $db; self::$latest = $this; }
    public function fetch($id)
    {
        if (self::$fetchError) { throw new RuntimeException('Injected SQL fault', self::$fetchError); }
        if (self::$optionalSqlError) { $this->errors[] = 'Simulated swallowed fetch_optionals SQL error'; }
        return self::$result;
    }
    public function loadRights($tag = '', $refresh = 0)
    {
        foreach (array('user_rights', 'group_rights') as $table) {
            // Core's void return can conceal both query failures; no exception.
            $res = $this->db->query('SELECT permission FROM test_'.$table);
            if (!$res) { continue; }
            $count = $this->db->num_rows($res);
            for ($i = 0; $i < $count; ++$i) { $this->db->fetch_object($res); }
            $this->db->free($res);
        }
    }
}
require_once __DIR__.'/../../class/booking.class.php';
class F29BookingProbe extends MsmBookingService
{
    public function reload($user, $strict) { return $this->currentUser($user, $strict); }
    public function mayRollback(Throwable $e) { return $this->coreGuardedBusinessRollbackAllowed($e); }
}
$passed = 0;
function f29Verify($yes, $label) { global $passed; if (!$yes) { throw new RuntimeException('FAIL: '.$label); } ++$passed; }
function f29Fails(callable $fn, $code)
{
    try { $fn(); }
    catch (RuntimeException $e) { f29Verify($e->getMessage() === $code, 'expected '.$code); return; }
    throw new RuntimeException('Missing error '.$code);
}
function f29Fixture()
{
    User::$result=1; User::$fetchError=0; User::$optionalSqlError=false;
    $db=new F29Database(); return array($db, new F29BookingProbe($db), (object) array('id'=>17));
}
list($db,$service,$caller)=f29Fixture();
$loaded=$service->reload($caller,true);
f29Verify($loaded instanceof User && $loaded->db === $db
    && $db->calls === array('user','group'), 'same real DoliDB restored on successful strict reload');
f29Verify($service->mayRollback(new RuntimeException('NotEnoughPermissions')), 'normal permission refusal still qualified');
f29Verify(!$service->mayRollback(new RuntimeException('MSMTransactionUnknown')), 'SQL failure forbids any business rollback');
foreach (array(0 => 'missing', -1 => 'SQL error', -3 => 'nested fetch error') as $ret => $description) {
    list($db,$service,$caller)=f29Fixture(); User::$result=$ret;
    f29Fails(static function()use($service,$caller){$service->reload($caller,true);},
        $ret<0 ? 'MSMTransactionUnknown' : 'NotEnoughPermissions');
    f29Verify($db->calls === array() && User::$latest->db === $db, $description.' stops before rights');
}
list($db,$service,$caller)=f29Fixture(); User::$optionalSqlError=true;
f29Fails(static function()use($service,$caller){$service->reload($caller,true);}, 'MSMTransactionUnknown');
f29Verify($db->calls === array() && User::$latest->db === $db,
    'successful outer fetch must not conceal Core optional-field SQL error');
list($db,$service,$caller)=f29Fixture(); User::$optionalSqlError=true;
$legacy=$service->reload($caller,false);
f29Verify($legacy instanceof User && $legacy->db === $db,
    'legacy profile reload retains pre-existing Core optional-field behavior');
foreach (array(1213,1205) as $errno) {
    list($db,$service,$caller)=f29Fixture(); User::$fetchError=$errno;
    f29Fails(static function()use($service,$caller){$service->reload($caller,true);}, 'MSMTransactionUnknown');
    f29Verify($db->calls === array() && !$service->mayRollback(new RuntimeException('MSMTransactionUnknown')),
        'thrown fetch SQL '.$errno.' no qualified rollback');
}
foreach (array('user','group') as $site) {
    foreach (array('query','throw','num_rows','num_rows_negative','fetch_object','fetch_object_null') as $mode) {
        list($db,$service,$caller)=f29Fixture();
        $db->faultAt=$site; $db->faultMode=$mode; $db->faultCode=$site==='user' ? 1213 : 1205;
        f29Fails(static function()use($service,$caller){$service->reload($caller,true);}, 'MSMTransactionUnknown');
        f29Verify(User::$latest->db === $db, 'DB restored '.$site.'/'.$mode);
        f29Verify($db->calls === ($site==='user' ? array('user') : array('user','group')),
            'rights reload stops at first SQL error '.$site.'/'.$mode);
        f29Verify(!$service->mayRollback(new RuntimeException('MSMTransactionUnknown')),
            'never business rollback qualified '.$site.'/'.$mode);
    }
}
// Do not change EnhancedMySQL and MariaDBSnapshot's historical user reload.
list($db,$service,$caller)=f29Fixture(); User::$result=-1;
f29Fails(static function()use($service,$caller){$service->reload($caller,false);},'NotEnoughPermissions');
list($db,$service,$caller)=f29Fixture(); $db->faultAt='user'; $db->faultMode='query';
$loaded=$service->reload($caller,false);
f29Verify($loaded instanceof User && $loaded->db === $db
    && $db->calls === array('user','group'), 'existing profile reload remains unchanged');
echo 'F29-01 strict CoreGuarded user/rights SQL: '.$passed.' assertions PASS (synthetic, no DB/HTTP).'.PHP_EOL;
