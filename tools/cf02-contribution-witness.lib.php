<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Pure, fail-closed comparison of read-only lab snapshots. Never opens a DB.
 */
final class Cf02ContributionWitnessContract
{
    const SCHEMA = 'dolibarr_i105_v24';
    const ACCOUNT = 'dolibarr_i105_v24_limited@10.0.2.100';
    const MARKER_PATTERN = '/^CF02LAB-[A-Z0-9]{8,16}$/D';
    const COUNTS = array('adherent', 'subscription', 'actioncomm', 'actioncomm_resources',
        'societe', 'socpeople', 'facture', 'facturedet', 'element_element',
        'paiement', 'paiement_facture', 'bank');

    public static function requireTrue($condition, $reason)
    {
        if (!$condition) { throw new RuntimeException($reason); }
    }

    public static function parameters($id, $marker, $start, $end, $amount, $agenda)
    {
        self::requireTrue(is_string($id) && preg_match('/^[1-9][0-9]{0,8}$/D', $id) === 1, 'CF02InvalidMemberId');
        self::requireTrue(is_string($marker) && preg_match(self::MARKER_PATTERN, $marker) === 1, 'CF02InvalidMarker');
        self::requireTrue(is_string($start) && is_string($end) && preg_match('/^20[5-9][0-9]-[0-9]{2}-[0-9]{2}$/D', $start) === 1
            && preg_match('/^20[5-9][0-9]-[0-9]{2}-[0-9]{2}$/D', $end) === 1
            && self::validDay($start) && self::validDay($end) && $end > $start, 'CF02InvalidPeriod');
        self::requireTrue(is_string($amount) && preg_match('/^(?:0|[1-9][0-9]{0,5})(?:\.[0-9]{1,2})?$/D', $amount) === 1,
            'CF02InvalidAmount');
        self::requireTrue($agenda === 'on' || $agenda === 'off', 'CF02InvalidAgendaMode');
        return array('member_id' => (int) $id, 'marker' => $marker, 'start' => $start,
            'end' => $end, 'amount' => $amount, 'agenda' => $agenda);
    }

    private static function validDay($day)
    {
        $parts = explode('-', $day);
        return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
    }

    public static function assertBaseline(array $s, array $e)
    {
        self::requireTrue(($s['identity']['schema'] ?? null) === self::SCHEMA
            && ($s['identity']['account'] ?? null) === self::ACCOUNT
            && ($s['identity']['version'] ?? null) === '8.4.13'
            && ($s['identity']['autocommit'] ?? null) === '1'
            && ($s['identity']['completion'] ?? null) === 'NO_CHAIN', 'CF02LabIdentityMismatch');
        self::requireTrue(($s['profile']['profile'] ?? null) === 'CoreGuarded'
            && ($s['profile']['family'] ?? null) === 'mysql'
            && !empty($s['profile']['raw_hash']), 'CF02WrongProfile');
        $m = $s['member'] ?? array();
        self::requireTrue(($m['id'] ?? null) === $e['member_id']
            && ($m['login'] ?? null) === $e['marker']
            && ($m['lastname'] ?? null) === $e['marker']
            && ($m['firstname'] ?? null) === 'Synthetic'
            && ($m['email_hash'] ?? null) === hash('sha256', strtolower($e['marker']).'@example.invalid')
            && ($m['entity'] ?? null) === 1
            && ($m['fk_soc'] ?? null) === 0
            && ($m['status'] ?? null) === 1
            && ($m['type_id'] ?? 0) > 0, 'CF02FixtureNotIsolated');
        self::requireTrue(($s['type']['id'] ?? null) === $m['type_id']
            && ($s['type']['status'] ?? null) === 1
            && is_numeric($s['type']['amount'] ?? null)
            && abs((float) $s['type']['amount'] - (float) $e['amount']) < 0.000001,
            'CF02TypeAmountMismatch');
        self::requireTrue(($s['agenda_enabled'] ?? null) === ($e['agenda'] === 'on'), 'CF02AgendaModeMismatch');
        self::requireTrue(count($s['subscriptions'] ?? array()) === 0
            && count($s['agenda_events'] ?? array()) === 0
            && count($s['agenda_resources'] ?? array()) === 0,
            'CF02FixtureAlreadyProcessed');
        $needed = array('adherent', 'adherent_type', 'subscription');
        if ($e['agenda'] === 'on') { $needed = array_merge($needed, array('actioncomm', 'actioncomm_resources')); }
        foreach ($needed as $table) {
            self::requireTrue(($s['engines'][$table] ?? null) === 'InnoDB', 'CF02NonTransactionalTable');
        }
        foreach (self::COUNTS as $table) {
            self::requireTrue(isset($s['counts'][$table]) && is_int($s['counts'][$table]) && $s['counts'][$table] >= 0,
                'CF02MissingProtectedCount');
        }
    }

    /** Compare a distinct post-HTTP readback. Do not infer physical ACK from rows. */
    public static function assertPositive(array $before, array $after, array $expected)
    {
        self::assertBaseline($before, $expected);
        self::requireTrue($before['identity'] === ($after['identity'] ?? null)
            && $before['profile'] === ($after['profile'] ?? null)
            && $before['type'] === ($after['type'] ?? null)
            && $before['engines'] === ($after['engines'] ?? null)
            && $before['source_hashes'] === ($after['source_hashes'] ?? null), 'CF02ContextChanged');
        $m = $before['member']; $new = $after['member'] ?? array();
        foreach ($m as $key => $value) {
            if ($key === 'datefin') { continue; }
            self::requireTrue(array_key_exists($key, $new) && $new[$key] === $value,
                'CF02UnexpectedMemberMutation');
        }
        self::requireTrue(($new['datefin'] ?? null) === $expected['end'].' 00:00:00', 'CF02MemberEndMismatch');
        $subs = $after['subscriptions'] ?? array();
        self::requireTrue(count($subs) === 1, 'CF02SubscriptionCountMismatch');
        $sub = $subs[0];
        self::requireTrue((int) ($sub['id'] ?? 0) > 0
            && ($sub['member_id'] ?? null) === $expected['member_id']
            && ($sub['type_id'] ?? null) === $m['type_id']
            && isset($sub['amount']) && is_numeric($sub['amount'])
            && abs((float) $sub['amount'] - (float) $expected['amount']) < 0.000001
            && ($sub['start'] ?? null) === $expected['start'].' 00:00:00'
            && ($sub['end'] ?? null) === $expected['end'].' 00:00:00'
            && ($sub['note_contains_marker'] ?? null) === true,
            'CF02SubscriptionPostconditionMismatch');
        foreach (self::COUNTS as $table) {
            $delta = $table === 'subscription' ? 1 :
                (($table === 'actioncomm' || $table === 'actioncomm_resources') && $expected['agenda'] === 'on' ? 1 : 0);
            self::requireTrue(($after['counts'][$table] ?? null) === $before['counts'][$table] + $delta,
                'CF02UnexpectedBusinessSideEffect');
        }
        $events = $after['agenda_events'] ?? array();
        $resources = $after['agenda_resources'] ?? array();
        $on = $expected['agenda'] === 'on';
        self::requireTrue(count($events) === ($on ? 1 : 0) && count($resources) === ($on ? 1 : 0),
            'CF02AgendaPostconditionMismatch');
        if ($on) {
            self::requireTrue(($events[0]['code'] ?? null) === 'AC_MEMBER_SUBSCRIPTION_CREATE'
                && ($events[0]['member_id'] ?? null) === $expected['member_id']
                && ($resources[0]['agenda_id'] ?? null) === $events[0]['id']
                && ($resources[0]['element_type'] ?? null) === 'user'
                && ($resources[0]['actor_id'] ?? 0) > 0,
                'CF02AgendaActorMismatch');
        }
        return array('status' => 'PASS', 'case' => 'POS01', 'subscription_id' => $sub['id'],
            'agenda_count' => count($events), 'member_end' => $new['datefin'],
            'outcome' => 'persisted_core_contribution_observed',
            'acknowledgement' => 'not_determined_by_database_readback');
    }
}
