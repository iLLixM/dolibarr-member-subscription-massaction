<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

require_once __DIR__.'/corecompatibility.class.php';
$msmHookCompatible = true;
try {
	MsmCoreCompatibility::load('core/class/commonhookactions.class.php', 'CommonHookActions', array());
	MsmCoreCompatibility::extension('CommonHookActions', 'addMoreMassActions', 4, array(1, 2));
	MsmCoreCompatibility::extension('CommonHookActions', 'doMassActions', 4, array(1, 2));
} catch (Throwable $e) { $msmHookCompatible = false; }
require_once __DIR__.'/../lib/membersubscriptionmassaction.lib.php';

/** Member-list entry point for the technical selection assistant. */
if ($msmHookCompatible) {
class ActionsMemberSubscriptionMassaction extends CommonHookActions
{
	const ACTION = 'memberSubscriptionMassactionStart';
	/** @var DoliDB */
	public $db;

	/** @param DoliDB $db Database handler */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Append an option without replacing the core options.
	 * @param array $parameters Hook context
	 * @param mixed $object Hook object
	 * @param string $action Current action
	 * @param HookManager|null $hookmanager Hook manager
	 * @return int
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager = null)
	{
		global $user, $langs;
		$this->resprints = '';
		if (($parameters['currentcontext'] ?? '') !== 'memberlist' || !msmCanUse($user)) {
			return 0;
		}
		$langs->load('membersubscriptionmassaction@membersubscriptionmassaction');
		$this->resprints = '<option value="'.self::ACTION.'">'.dol_escape_htmltag($langs->trans('MSMAction')).'</option>';
		return 0;
	}

	/**
	 * Dispatch only the confirmed member-list POST, before the core sends headers.
	 * @param array $parameters Hook parameters
	 * @param mixed $object Hook object
	 * @param string $action Current action
	 * @param HookManager|null $hookmanager Hook manager
	 * @return int
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager = null)
	{
		global $user, $conf, $langs;
		if (($parameters['currentcontext'] ?? '') !== 'memberlist' || ($parameters['massaction'] ?? '') !== self::ACTION) {
			return 0;
		}
		$langs->load('membersubscriptionmassaction@membersubscriptionmassaction');
		if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !GETPOST('confirmmassaction', 'alpha', 2)
			|| !msmCanUse($user) || !msmValidCsrf($_POST['token'] ?? null, $_SESSION)) {
			accessforbidden();
		}
		try {
			// Validate raw shape before integer normalization can hide malformed IDs.
			$ids = msmSelectionIds($_POST['toselect'] ?? null, getDolGlobalInt('MAIN_LIMIT_FOR_MASS_ACTIONS', 1000));
			if ($ids !== msmSelectionIds($parameters['toselect'] ?? null, getDolGlobalInt('MAIN_LIMIT_FOR_MASS_ACTIONS', 1000))) {
				throw new InvalidArgumentException('MSMInvalidSelection');
			}
			msmCheckMembers($this->db, $user, $ids);
			$token = msmCreateRun($_SESSION, $ids, (int) $user->id, (int) $conf->entity, dol_now());
		} catch (Exception $e) {
			$this->error = $langs->trans('MSMSelectionUnavailable');
			return -1;
		}
		header('Location: '.dol_buildpath('/membersubscriptionmassaction/subscription.php', 1).'?run='.$token, true, 303);
		exit;
	}
}
}
else {
	/** Disabled adapter: no inheritance or business effects on incompatible Cores. */
	class ActionsMemberSubscriptionMassaction
	{
		public $resprints = '';
		public $error = 'MSMCoreContractUnsupported';
		public $errors = array('MSMCoreContractUnsupported');
		public function __construct($db) {}
		public function addMoreMassActions($parameters, &$object, &$action, $hookmanager = null) { return 0; }
		public function doMassActions($parameters, &$object, &$action, $hookmanager = null)
		{
			return ($parameters['massaction'] ?? '') === 'memberSubscriptionMassactionStart' ? -1 : 0;
		}
	}
}
unset($msmHookCompatible);
