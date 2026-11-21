<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
/** Synthetic FormMail boundary; no database or transport. */
class FormMail
{
	public function __construct($db) {}
	public function getEMailTemplate($db, $type, $user, $langs, $id, $active, $label)
	{
		$GLOBALS['template_request'] = array($type, $label);
		return (object) array('id' => 1, 'topic' => 'Member __REF__ __EXTRA__', 'content' => (empty($GLOBALS['plain_template']) ? '<p>__SUBSCRIPTION_DESCRIPTION__ __SUBSCRIPTION_START__ __SUBSCRIPTION_END__ __SUBSCRIPTION_AMOUNT__ __REF__</p>' : '__SUBSCRIPTION_DESCRIPTION__'), 'joinfiles' => 1);
	}
}
