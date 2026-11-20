<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Bounded CLI-only loaded-Core gate. Does not create business objects.
 */
if (PHP_SAPI !== 'cli' || !in_array($argv[1] ?? '', array('--check-only', '--empty-transaction'), true)) {
    fwrite(STDERR, "Usage: CF02_DOLIBARR_ROOT=/path/to/core sh -c 'php tools/cf02-loaded-core-entry.php --check-only'\n");
    exit(64);
}
$mode = $argv[1];
$report = array('status' => 'STOP', 'mode' => $mode, 'type' => 'bounded loaded-Core empty-transaction test; no business DML',
    'failure' => 'unknown', 'source' => 'development branch', 'core' => '', 'mysql' => '', 'completion' => '');
$db = null;
try {
    if ($mode === '--empty-transaction' && getenv('CF02_ACK_EMPTY_TRANSACTION') !== 'YES') {
        throw new LogicException('Empty Core transaction needs explicit CF02_ACK_EMPTY_TRANSACTION=YES');
    }
    $root = realpath(getenv('CF02_DOLIBARR_ROOT') ?: '');
    if ($root === false || !is_file($root.'/htdocs/version.inc.php') || !is_file($root.'/htdocs/conf/conf.php')) {
        throw new LogicException('Expected a real Dolibarr checkout with htdocs/config');
    }
    define('DOL_DOCUMENT_ROOT', $root.'/htdocs');
    define('DOL_URL_ROOT', '');
    require_once DOL_DOCUMENT_ROOT.'/conf/conf.php';
    require_once DOL_DOCUMENT_ROOT.'/version.inc.php';
    require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
    require_once DOL_DOCUMENT_ROOT.'/core/class/conf.class.php';
    require_once DOL_DOCUMENT_ROOT.'/core/class/translate.class.php';
    require_once DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php';
    require_once dirname(__DIR__).'/class/coreguardedtransaction.class.php';
    if (($dolibarr_main_db_type ?? '') !== 'mysqli'
        || ($dolibarr_main_db_name ?? '') !== 'dolibarr_i105_v24'
        || ($dolibarr_main_db_user ?? '') !== 'dolibarr_i105_v24_limited') {
        throw new LogicException('Wrong DB type/schema/account: do not run a Core transaction');
    }
    $conf = new Conf();
    $conf->db->character_set = 'utf8mb4';
    $conf->db->dolibarr_main_db_collation = 'utf8mb4_unicode_ci';
    $conf->file->dol_document_root = array('main' => DOL_DOCUMENT_ROOT);
    $langs = new Translate('', $conf);
    $db = new DoliDBMysqli('mysqli', $dolibarr_main_db_host, $dolibarr_main_db_user,
        $dolibarr_main_db_pass, $dolibarr_main_db_name, (int) $dolibarr_main_db_port);
    if (empty($db->connected) || empty($db->database_selected)) {
        throw new LogicException('Real restricted DB unavailable');
    }
    $report['core'] = defined('DOL_VERSION') ? DOL_VERSION : 'unknown';
    $tx = new MsmCoreGuardedTransaction($db);
    $idle = $tx->beforeBegin();
    if ($idle['state'] !== MsmDatabaseState::IDLE || !$idle['fresh']) {
        throw new LogicException('Restricted Core admission preflight refused');
    }
    $report['completion'] = $idle['completion_mode'];
    if ($mode === '--empty-transaction') {
        $tx->begin();  // D-01 SET -> exact Core BEGIN, with no intervening SQL
        $running = $tx->afterBegin();
        if ($running['state'] !== MsmDatabaseState::ACTIVE) {
            throw new LogicException('Core BEGIN acknowledgement inconsistent');
        }
        $tx->beforeCompletion();
        $ack = $db->rollback(); // Only one clearly owned empty Core transaction.
        $end = $tx->completed('ROLLBACK', $ack);
        if ($end['state'] !== MsmDatabaseState::IDLE || !$end['fresh']) {
            throw new LogicException('Core ROLLBACK acknowledgement uncertain');
        }
        $report['can_continue'] = $tx->nonterminalAcknowledged();
    }
    $report['status'] = 'PASS';
    $report['failure'] = null;
} catch (Throwable $e) {
    // Never attempt a cleanup/retry/foreign rollback after uncertainty.
    $report['failure'] = ($e instanceof LogicException) ? $e->getMessage() : 'Core/DB runtime check failed';
}
// On CHAIN/RELEASE an automatic connection teardown at process exit is intended;
// never start another transaction or use the same connection for recovery.
echo json_encode($report, JSON_UNESCAPED_SLASHES)."\n";
exit($report['status'] === 'PASS' ? 0 : 1);
