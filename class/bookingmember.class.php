<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/corecompatibility.class.php';
MsmCoreCompatibility::load('adherents/class/adherent.class.php', 'Adherent', array('update_end_date' => 1));
MsmCoreCompatibility::extension('Adherent', 'update_end_date', 1);
/** Observe R16 without changing the core API or bypassing its triggers. */
if (!class_exists('MsmBookingMember', false)) {
class MsmBookingMember extends Adherent
{
	public $msmEndUpdate = null;
	public $msmCoreEnd = null;
	public function update_end_date($user)
	{
		$this->msmEndUpdate = null; $this->msmCoreEnd = null;
		$this->msmEndUpdate = parent::update_end_date($user);
		// A successful Core call may deliberately clear the end; the return value
		// distinguishes that observation from a missing or failed call.
		if ($this->msmEndUpdate > 0) { $this->msmCoreEnd = ($this->datefin === null || $this->datefin === '') ? null : $this->db->idate($this->datefin); }
		return $this->msmEndUpdate;
	}
}
}
