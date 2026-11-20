<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
/**
 * Per-User SQL guard for Dolibarr User::loadRights().
 *
 * Core's void rights reload can silently skip failed user/group SQL queries.
 * Delegate every operation to the original DoliDB and stop immediately at the
 * first failed SELECT. This object exists only during the CoreGuarded reload.
 * No new connection, transaction, SQL write, or process-global error setting.
 */
final class MsmCoreGuardedRightsDb
{
    private $inner;

    public function __construct($db) { $this->inner = $db; }

    public function query($sql, ...$args)
    {
        try { $result = $this->inner->query($sql, ...$args); }
        catch (Throwable $e) { throw new RuntimeException('MSMTransactionUnknown', 0, $e); }
        if (!$result) { throw new RuntimeException('MSMTransactionUnknown'); }
        return $result;
    }

    public function __call($name, $args)
    {
        try { $result = $this->inner->$name(...$args); }
        catch (Throwable $e) { throw new RuntimeException('MSMTransactionUnknown', 0, $e); }
        // Core loadRights() silently treats a negative num_rows() as an
        // empty result set. A valid DoliDB row count must be a nonnegative int.
        if ($name === 'num_rows' && (!is_int($result) || $result < 0)) {
            throw new RuntimeException('MSMTransactionUnknown');
        }
        // A row was promised by num_rows(): NULL/false is a read failure,
        // not a harmless absence of rights (zero rows are never fetched).
        if ($name === 'fetch_object' && !is_object($result)) {
            throw new RuntimeException('MSMTransactionUnknown');
        }
        return $result;
    }

    public function __get($name) { return $this->inner->$name; }
    public function __isset($name) { return isset($this->inner->$name); }
    public function __set($name, $value) { $this->inner->$name = $value; }
}
