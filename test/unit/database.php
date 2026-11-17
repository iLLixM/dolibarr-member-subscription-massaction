<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../fixtures/database_state.php';
// Synthetic protocol vectors derived from G0-A/B/C/D. No database/transport bootstrap.
class ObservationDatabase
{
	use DatabaseStateModel;
	public $type = 'mysqli'; public $transaction_opened = 0; public $active = false;
	public $engine = 'InnoDB'; public $queries = array(); public $controls = array();
	public function __construct() { $this->observationInit(); $this->family = 'mysql'; }
	public function prefix() { return 'test_'; }
	public function plimit($n) { return ' LIMIT '.$n; }
	public function escape($s) { return $s; }
	public function free($r) {}
	public function fetch_object($r) { return array_shift($r->rows); }
	public function query($sql)
	{
		$this->queries[] = $sql;
		$r = $this->observationQuery($sql); if ($r !== null) { return $r; }
		if (preg_match("/^SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE\(\) AND TABLE_NAME = 'test_[a-z_]+'$/D", $sql)) {
			return (object) array('rows' => $this->engine === null ? array() : array((object) array('engine' => $this->engine)));
		}
		throw new LogicException('Unexpected synthetic observation SQL: '.$sql);
	}
	public function begin() { $this->controls[] = 'begin'; if ($this->transaction_opened++ === 0) { $this->active = true; $this->modelBegin(); } return 1; }
	public function commit() { $this->controls[] = 'commit'; if (--$this->transaction_opened === 0) { $this->active = false; $this->modelEnd('COMMITTED'); } return 1; }
	public function rollback() { $this->controls[] = 'rollback'; $this->transaction_opened = 0; $this->active = false; $this->modelEnd('ROLLED BACK'); return 1; }
}
$checks = 0;
function checkState($ok, $name) { global $checks; if (!$ok) { throw new RuntimeException('FAIL: '.$name); } $checks++; }
function idleFixture() { $db = new ObservationDatabase(); $s = new SyntheticDatabaseState($db); checkState($s->beforeBegin()['state'] === MsmDatabaseState::IDLE, 'fresh idle fixture'); return array($db, $s); }
function activeFixture() { list($db, $s) = idleFixture(); $db->begin(); checkState($s->afterBegin()['state'] === MsmDatabaseState::ACTIVE, 'own active fixture'); return array($db, $s); }
// Production never accepts a test handle or an unsupported driver.
$db = new ObservationDatabase();
checkState((new MsmDatabaseState($db))->beforeBegin()['reason'] === 'MSMDatabaseUnsupported', 'production rejects non-native mysqli');
foreach (array(array('mysql','8.4.13','MySQL Community Server - GPL',true), array('mysql','8.4.13','MySQL Enterprise Server - Commercial',true), array('mariadb','10.11.19-MariaDB','Debian 12',true), array('mysql','8.4.13','Percona Server',false), array('mysql','8.4.13-Percona','MySQL Community Server - GPL',false), array('mysql','8.4.13-MariaDB','MySQL Community Server - GPL',false), array('mysql','8.4.13','unknown',false)) as $c) {
	$db = new ObservationDatabase(); $db->family = $c[0]; $db->versionOverride = $c[1]; $db->commentOverride = $c[2];
	$r = (new SyntheticDatabaseState($db))->beforeBegin();
	checkState(($r['state'] === MsmDatabaseState::IDLE) === $c[3], 'server family '.$c[1].'/'.$c[2]);
}
$db = new ObservationDatabase(); $db->family = 'mariadb'; $db->psDenied = true;
$s = new SyntheticDatabaseState($db); checkState($s->beforeBegin()['continuity'] === 'snapshot', 'MariaDB honest snapshot contract');
$db->begin(); checkState($s->afterBegin()['state'] === 'ACTIVE', 'MariaDB active without P_S grant'); $db->rollback();
checkState($s->completed('ROLLBACK', true)['state'] === 'IDLE' && !preg_grep('/performance_schema|SAVEPOINT/', $db->queries), 'MariaDB rollback and no PS query');
// Missing initial event requires a real engine row; an empty contribution table is irrelevant.
list($db, $s) = idleFixture();
checkState(!preg_grep('/subscription|BEGIN|COMMIT|ROLLBACK/', $db->queries) && !$db->controls, 'bootstrap only real member read, no transaction control');
foreach (array('missing','stale','empty-members','no-table','myisam','autocommit-off','ps-off','denied','partial','float-id','invalid-counter','bad-thread','sql-false','unsupported-driver') as $fault) {
	$db = new ObservationDatabase(); $db->modelEvent('COMMITTED');
	if ($fault === 'missing') { $db->event = null; $db->instrumented = false; }
	if ($fault === 'stale') { $db->instrumented = false; }
	if ($fault === 'empty-members') { $db->membersPresent = false; }
	if ($fault === 'no-table') { $db->engine = null; }
	if ($fault === 'myisam') { $db->engine = 'MyISAM'; }
	if ($fault === 'autocommit-off') { $db->autocommit = '0'; }
	if ($fault === 'ps-off') { $db->performanceSchema = '0'; }
	if ($fault === 'denied') { $db->psDenied = true; }
	if ($fault === 'partial') { unset($db->event->NUMBER_OF_RELEASE_SAVEPOINT); }
	if ($fault === 'float-id') { $db->event->EVENT_ID = 1.0e18; }
	if ($fault === 'invalid-counter') { $db->event->NUMBER_OF_SAVEPOINTS = '-1'; }
	if ($fault === 'bad-thread') { $db->event->THREAD_ID = '999'; }
	if ($fault === 'sql-false') { $db = new class extends ObservationDatabase { public function query($sql) { return false; } }; }
	if ($fault === 'unsupported-driver') { $db->type = 'pgsql'; }
	$r = (new SyntheticDatabaseState($db))->beforeBegin();
	checkState($r['state'] === 'UNKNOWN' && !$r['fresh'] && !$db->controls, 'fail-closed bootstrap '.$fault);
}
// Uninstrumented foreign transaction is not adopted, even after re-enabling instrumentation.
foreach (array(false,true) as $reenabled) {
	$db = new ObservationDatabase(); $db->modelEvent('COMMITTED'); $db->active = true; $db->instrumented = $reenabled;
	$s = new SyntheticDatabaseState($db); checkState($s->beforeBegin()['state'] === 'UNKNOWN' && !$db->controls && $db->active, 'foreign uninstrumented untouched '.$reenabled);
}
list($db, $s) = activeFixture(); $token = $db->event->EVENT_ID;
$db->begin(); $db->begin(); $db->commit(); $db->commit();
checkState($s->active()['state'] === 'ACTIVE' && $db->event->EVENT_ID === $token, 'nested Core depth preserves event');
checkState(!$db->markers && $db->event->NUMBER_OF_SAVEPOINTS === $db->event->NUMBER_OF_RELEASE_SAVEPOINT, 'unique markers released');
foreach (array('save-error','release-error','stale-save','stale-release','omit-release','replace-on-save') as $fault) {
	list($db, $s) = activeFixture(); $db->probeFault = $fault;
	$r = $s->active(); checkState($r['state'] === 'UNKNOWN' && !$r['owned'], 'checkpoint fails '.$fault);
	$count = count($db->queries); $db->probeFault = '';
	checkState($s->rollbackContext()['state'] === 'UNKNOWN' && count($db->queries) === $count, 'uncertain marker/context cannot be redeemed or retried '.$fault);
}
foreach (array('stale-counter','off-commit-begin-on','new-event','handle','native-id','thread','uuid','lost','autocommit','depth','unexpected-commit') as $fault) {
	list($db, $s) = activeFixture();
	if ($fault === 'stale-counter') { $db->currentInstrumented = false; }
	if ($fault === 'off-commit-begin-on') { $db->instrumented = false; $db->commit(); $db->begin(); $db->instrumented = true; }
	if ($fault === 'new-event') { $db->modelEvent('ACTIVE'); }
	if ($fault === 'handle') { $db->native = clone $db->native; }
	if ($fault === 'native-id') { $db->native->thread_id = '52'; }
	if ($fault === 'thread') { $db->threadId = '82'; }
	if ($fault === 'uuid') { $db->serverUuid = 'replacement'; }
	if ($fault === 'lost') { $db->lost = true; }
	if ($fault === 'autocommit') { $db->autocommit = '0'; }
	if ($fault === 'depth') { $db->transaction_opened = 2; }
	if ($fault === 'unexpected-commit') { $db->commit(); }
	checkState($s->active()['state'] === 'UNKNOWN', 'lost identity/freshness '.$fault);
	checkState($s->rollbackContext()['state'] === 'UNKNOWN', 'no foreign rollback '.$fault);
}
// G0 owned windows: a previously instrumented surviving transaction may still
// have fresh counters while instrumentation for NEW transactions is disabled.
list($db, $s) = activeFixture(); $db->instrumented = false;
checkState($s->active()['state'] === 'ACTIVE', 'surviving instrumented context still fresh after flag off');
list($db, $s) = activeFixture();
checkState($s->afterBegin()['state'] === 'UNKNOWN' && $s->rollbackContext()['state'] === 'UNKNOWN', 'afterBegin cannot overwrite an owned token');
$db = new ObservationDatabase(); $db->begin();
checkState((new SyntheticDatabaseState($db))->afterBegin()['state'] === 'UNKNOWN', 'no ownership without successful idle preflight');
// Lossless BIGINT handling above PHP_INT_MAX, including carry across a decimal boundary.
list($db, $s) = idleFixture(); $db->eventSequence = '9999999999999999999'; $db->begin();
checkState($s->afterBegin()['transaction'] === '10000000000000000000', 'BIGINT event identity lossless');
$db->event->NUMBER_OF_SAVEPOINTS = '9999999999999999999'; $db->event->NUMBER_OF_RELEASE_SAVEPOINT = '18446744073709551613';
checkState($s->active()['state'] === 'ACTIVE' && $db->event->NUMBER_OF_SAVEPOINTS === '10000000000000000000' && $db->event->NUMBER_OF_RELEASE_SAVEPOINT === '18446744073709551614', 'BIGINT counters lossless');
foreach (array('COMMIT','ROLLBACK') as $op) {
	list($db, $s) = activeFixture(); $token = $db->event->EVENT_ID;
	$op === 'COMMIT' ? $db->commit() : $db->rollback(); $offset = count($db->queries);
	$ended = $s->completed($op, true);
	checkState($ended['state'] === 'IDLE' && $ended['completed_transaction'] === $token && $ended['completed_operation'] === $op, 'confirmed own completion '.$op);
	$queries = array_slice($db->queries, $offset);
	checkState(strpos($queries[1], 'events_transactions_current') !== false && strpos(implode('\n',array_slice($queries,2)), 'SELECT rowid') !== false && $db->event->EVENT_ID !== $token, 'own completion before new idle '.$op);
	checkState($s->beforeBegin()['state'] === 'IDLE', 'next member context allowed after confirmed '.$op);
}
foreach (array('unacknowledged','wrong-kind','completion-replaced','post-idle-stale','post-idle-empty','post-loss') as $fault) {
	list($db, $s) = activeFixture(); $db->commit();
	if ($fault === 'completion-replaced') { $db->modelEvent('COMMITTED'); }
	if ($fault === 'post-idle-stale') { $db->instrumented = false; }
	if ($fault === 'post-idle-empty') { $db->membersPresent = false; }
	if ($fault === 'post-loss') { $db->lost = true; }
	$r = $s->completed($fault === 'wrong-kind' ? 'ROLLBACK' : 'COMMIT', $fault !== 'unacknowledged');
	checkState($r['state'] === 'UNKNOWN' && $s->beforeBegin()['state'] === 'UNKNOWN', 'uncertain completion stays unknown '.$fault);
}
checkState(MsmDatabaseState::FAILED === 'FAILED' && MsmDatabaseState::BUSY === 'BUSY', 'reserved backend states');
// I107-F02: policy uses the actual ACTIVE event, not the session default.
foreach (array('REPEATABLE READ','READ COMMITTED','SERIALIZABLE','READ UNCOMMITTED') as $isolation) {
	list($db, $s) = idleFixture(); $db->nextIsolation = $isolation; $db->begin();
	$active = $s->afterBegin();
	checkState($active['state'] === 'ACTIVE' && $active['fresh'] && $active['owned'] && $active['isolation'] === $isolation && $db->isolation === 'REPEATABLE READ', 'I107 actual one-shot event isolation '.$isolation);
	$count = count($db->queries); $denied = false;
	try { $s->requireSupportedIsolation($active); } catch (RuntimeException $e) { $denied = $e->getMessage() === 'MSMTransactionUnsupported'; }
	checkState($denied === ($isolation !== 'REPEATABLE READ') && count($db->queries) === $count, 'I107 SQL-free admission for '.$isolation);
	$context = $s->rollbackContext();
	checkState($context['state'] === 'ACTIVE' && $context['owned'] && $context['fresh'], 'I107 unsupported policy does not destroy own rollback context '.$isolation);
	$db->rollback(); checkState($s->completed('ROLLBACK', true)['state'] === 'IDLE', 'I107 expected-own rollback completion '.$isolation);
}
foreach (array(null,'','REPEATABLE-READ','invented',1,array()) as $badIsolation) {
	list($db, $s) = activeFixture();
	if ($badIsolation === null) { unset($db->event->ISOLATION_LEVEL); } else { $db->event->ISOLATION_LEVEL = $badIsolation; }
	$active = $s->active(); checkState($active['state'] === 'UNKNOWN' && !$active['owned'], 'I107 malformed isolation contract fails closed '.gettype($badIsolation));
	$count = count($db->queries); checkState($s->rollbackContext()['state'] === 'UNKNOWN' && count($db->queries) === $count, 'I107 malformed contract never redeems uncertain rollback');
}
list($db, $s) = activeFixture(); $db->event->ISOLATION_LEVEL = 'READ COMMITTED'; $db->native = clone $db->native;
checkState($s->active()['state'] === 'UNKNOWN' && $s->rollbackContext()['state'] === 'UNKNOWN', 'I107 invalid identity wins over known isolation policy error');
$db = new ObservationDatabase(); $db->family = 'mariadb'; $db->isolation = 'not a MySQL value'; $db->psDenied = true;
$s = new SyntheticDatabaseState($db); $s->beforeBegin(); $db->begin(); $active = $s->afterBegin(); $s->requireSupportedIsolation($active);
checkState($active['state'] === 'ACTIVE' && !preg_grep('/ISOLATION_LEVEL|performance_schema/', $db->queries), 'I107 MariaDB has no new isolation/PS requirement');

echo $checks." database observation assertions passed (synthetic protocol; no database or network).\n";
