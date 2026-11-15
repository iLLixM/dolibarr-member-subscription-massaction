<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
/** Source-verified policies, not runtime qualification of an installation. */
class MsmCoreCompatibility
{
	public static function profile($version)
	{
		// Exact pins: the member-end change is not shared by every V24 patch.
		if ($version === '22.0.5') { return array('latest_end' => false, 'pdf' => 'crabe'); }
		if ($version === '24.0.3') { return array('latest_end' => true, 'pdf' => 'sponge'); }
		throw new RuntimeException('MSMCoreVersionUnsupported');
	}
	public static function current()
	{
		return self::profile(defined('DOL_VERSION') ? DOL_VERSION : '');
	}
}
