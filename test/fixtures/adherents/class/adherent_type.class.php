<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
/** Synthetic membership type for the isolated mail adapter runner. */
class AdherentType
{
	public function __construct($db) {}
	public function fetch($id) { return 1; }
	public function getMailOnSubscription() { return 'Type text'; }
}
