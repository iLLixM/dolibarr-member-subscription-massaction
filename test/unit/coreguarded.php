<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../../class/coreguardedtransaction.class.php';
$passed = 0;
function cgVerify($condition, $label) { global $passed; if (!$condition) { throw new RuntimeException('FAIL: '.$label); } $passed++; }
function cgThrows(callable $fn, $reason)
{
	try { $fn(); } catch (RuntimeException $e) { cgVerify($e->getMessage() === $reason, 'exception '.$reason); return; }
	throw new RuntimeException('Missing exception '.$reason);
}
class CgFixtureDB
{
	public $type = 'mysqli'; public $transaction_opened = 0;
	public $handle; public $commands = array(); public $mode = 'NO_CHAIN';
	public $autocommit = '1'; public $setFail = false; public $onSet;
	public $beginFail = false; public $commitFail = false; public $rollbackFail = false;
	public $readFail = false; public $connectionId = '1451'; public $ack = 1;
	public $active = false; public $foreign = false; public $endResult;
	public function __construct() { $this->handle = (object) array('id'=>'1451'); }
	public function query($sql)
	{
		$this->commands[] = $sql;
		if (stripos($sql,'performance_schema') !== false) { throw new RuntimeException('Forbidden performance schema'); }
		if (strpos($sql,'SET TRANSACTION ISOLATION LEVEL') === 0) {
			if ($this->onSet) { $fn=$this->onSet; $fn($this); }
			if ($this->foreign) { throw new RuntimeException('MySQL 1568',1568); }
			if ($this->setFail) { return false; }
			return true;
		}
		if ($this->readFail) { return false; }
		if (strpos($sql,'SELECT CONNECTION_ID() AS cid') === 0) {
			return (object) array('rows'=>array((object) array('cid'=>$this->connectionId,'ac'=>$this->autocommit,'completion'=>$this->mode)));
		}
		if ($sql === 'SELECT @@SESSION.completion_type AS completion') {
			return (object) array('rows'=>array((object) array('completion'=>$this->mode)));
		}
		throw new LogicException('Unexpected SQL '.$sql);
	}
	public function fetch_object($res) { return array_shift($res->rows); }
	public function free($res) {}
	public function begin()
	{
		$this->commands[]='Core BEGIN';
		if ($this->beginFail) { return false; }
		if ($this->transaction_opened === 0) { $this->active=true; }
		$this->transaction_opened++; return 1;
	}
	public function commit()
	{
		$this->commands[]='Core COMMIT';
		if ($this->commitFail) { return false; }
		$this->transaction_opened--;
		if ($this->transaction_opened === 0) { $this->active=false; }
		if ($this->mode === 'RELEASE' && $this->transaction_opened === 0) { $this->handle->id = '0'; }
		return $this->ack;
	}
	public function rollback()
	{
		$this->commands[]='Core ROLLBACK';
		$this->transaction_opened=0; $this->active=false;
		return !$this->rollbackFail ? 1 : 0;
	}
}
class CgFixtureAdapter extends MsmCoreGuardedTransaction
{
	protected function nativeHandle() { return $this->fixture()->handle; }
	protected function nativeThread($handle) { return (string) $handle->id; }
	private $fixtureDb;
	public function __construct($db) { $this->fixtureDb = $db; parent::__construct($db); }
	private function fixture() { return $this->fixtureDb; }
}
function cgPrepared($mode)
{
	$db=new CgFixtureDB(); $db->mode=$mode; $tx=new CgFixtureAdapter($db);
	cgVerify($tx->beforeBegin()['state']===MsmDatabaseState::IDLE,'preflight '.$mode);
	return array($db,$tx);
}
foreach (array('NO_CHAIN','CHAIN','RELEASE','INVALID','0','1','2') as $mode) {
	list($db,$tx)=cgPrepared($mode);
	cgVerify($tx->begin() && $tx->afterBegin()['owned'],'begun '.$mode);
	cgVerify($db->commands[1]==='SET TRANSACTION ISOLATION LEVEL REPEATABLE READ' && $db->commands[2]==='Core BEGIN','adjacent SET/Core-BEGIN '.$mode);
	$db->begin(); $db->commit(); // Core nesting does not close the outer transaction.
	cgVerify($tx->active()['state']===MsmDatabaseState::ACTIVE,'Core nesting '.$mode);
	$tx->beforeCompletion();
	cgVerify($tx->completed('COMMIT',$db->commit())['state']===MsmDatabaseState::IDLE,'acknowledged COMMIT '.$mode);
	$expected=$mode==='NO_CHAIN'||$mode==='0';
	cgVerify($tx->nonterminalAcknowledged()===$expected,'completion planning '.$mode);
	cgVerify(!preg_grep('/performance_schema/i',$db->commands),'no P_S '.$mode);
	if (!$expected) { cgVerify(count(array_filter($db->commands,static function($s){return strpos($s,'SELECT')===0;}))===2,'no post-end SQL '.$mode); }
}
foreach (array('NO_CHAIN','CHAIN','RELEASE') as $mode) {
	list($db,$tx)=cgPrepared($mode); $tx->begin(); $tx->beforeCompletion();
	cgVerify($tx->completed('ROLLBACK',$db->rollback())['state']===MsmDatabaseState::IDLE,'ack rollback '.$mode);
	cgVerify($tx->nonterminalAcknowledged()===($mode==='NO_CHAIN'),'rollback STOP class '.$mode);
}
$db=new CgFixtureDB();$db->autocommit='0';$tx=new CgFixtureAdapter($db);
cgVerify($tx->beforeBegin()['state']===MsmDatabaseState::UNKNOWN&&!$db->active,'autocommit 0 refused');
$db=new CgFixtureDB();$db->transaction_opened=1;$tx=new CgFixtureAdapter($db);
cgVerify($tx->beforeBegin()['state']===MsmDatabaseState::UNKNOWN&&!$db->commands,'foreign Core depth refused without SQL');
$db=new CgFixtureDB();$db->foreign=true;$tx=new CgFixtureAdapter($db);$tx->beforeBegin();
cgThrows(static function() use($tx) {$tx->begin();},'MSMTransactionUnsupported');
cgVerify(!in_array('Core BEGIN',$db->commands,true) && !$tx->beginAttempted(),'MySQL 1568 before BEGIN, no foreign rollback');
$db=new CgFixtureDB();$db->beginFail=true;$tx=new CgFixtureAdapter($db);$tx->beforeBegin();
cgThrows(static function()use($tx){$tx->begin();},'MSMTransactionUnknown');
cgVerify($tx->beginAttempted()&&$tx->active()['state']===MsmDatabaseState::UNKNOWN,'lost BEGIN ack latched unknown');
foreach(array('commit','rollback') as $op){
 list($db,$tx)=cgPrepared('NO_CHAIN');$tx->begin();$tx->beforeCompletion();
 $ack=$op==='commit'?$db->commit():$db->rollback();
 cgVerify($tx->completed(strtoupper($op),0)['state']===MsmDatabaseState::UNKNOWN,'unacknowledged '.$op.' rejected');
 cgVerify(!$tx->nonterminalAcknowledged(),'no uncertain continuation '.$op);
}
list($db,$tx)=cgPrepared('NO_CHAIN');$tx->begin();$db->transaction_opened=2;
cgVerify($tx->active()['state']===MsmDatabaseState::UNKNOWN,'wrong nested depth STOP');
list($db,$tx)=cgPrepared('NO_CHAIN');$tx->begin();$db->handle->id='1452';
cgVerify($tx->active()['state']===MsmDatabaseState::UNKNOWN,'native reconnect STOP');
list($db,$tx)=cgPrepared('NO_CHAIN');$tx->begin();$db->mode='CHAIN';
cgThrows(static function()use($tx){$tx->beforeCompletion();},'MSMTransactionUnknown');
cgVerify($tx->rollbackContext()['state']===MsmDatabaseState::UNKNOWN,'detected completion rewrite disables rollback');
list($db,$tx)=cgPrepared('NO_CHAIN');$tx->begin();$db->readFail=true;
cgThrows(static function()use($tx){$tx->beforeCompletion();},'MSMTransactionUnknown');
list($db,$tx)=cgPrepared('NO_CHAIN');$tx->begin();$tx->beforeCompletion();$db->commitFail=true;
cgVerify($tx->completed('COMMIT',$db->commit())['state']===MsmDatabaseState::UNKNOWN,'lost COMMIT ack STOP');
list($db,$tx)=cgPrepared('NO_CHAIN');$tx->begin();$tx->beforeCompletion();$db->transaction_opened=2;
cgVerify($tx->completed('COMMIT',true)['state']===MsmDatabaseState::UNKNOWN,'unexpected depth after COMMIT');
echo 'CF-02 bounded Core adapter: '.$passed.' assertions PASS (synthetic; no live Dolibarr/MySQL).'.PHP_EOL;
