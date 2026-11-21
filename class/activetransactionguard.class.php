<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
/**
 * Minimal active-transaction observation contract for business services.
 *
 * Transaction begin/commit/rollback are deliberately not exposed here.
 * MsmBookingService remains the sole outer DoliDB transaction owner per member.
 * Evidence strength is selected by the concrete profile guard; do not infer
 * Physical MySQL event identity from CoreGuarded or MariaDB snapshots.
 */
interface MsmActiveTransactionGuard
{
	/** A guard is strictly bound to the booking owner's original DoliDB. */
	public function isFor($db);
	/** Read the currently owned transaction context; fail closed on uncertainty. */
	public function active();
	/** Apply the profile-specific isolation admission to that exact observation. */
	public function requireSupportedIsolation(array $state);
}
