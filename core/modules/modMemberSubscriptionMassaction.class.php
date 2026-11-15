<?php
/* Copyright (C) 2004-2018 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019 Nicolas ZABOURI <info@inovea-conseil.com>
 * Copyright (C) 2019-2024 Frédéric France <frederic.france@free.fr>
 * Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/** Bulk member subscriptions with optional Core invoicing, PDF and confirmation mail. */
class modMemberSubscriptionMassaction extends DolibarrModules
{
	/** @param DoliDB $db Database handler */
	public function __construct($db)
	{
		$this->db = $db;
		// Temporary, locally collision-checked ID. Reserve a public ID before release.
		$this->numero = 584620;
		$this->rights_class = 'membersubscriptionmassaction';
		$this->family = 'crm';
		$this->module_position = '90';
		$this->name = 'MemberSubscriptionMassaction';
		$this->description = 'MSMDescription';
		$this->version = '0.1.0-dev';
		$this->const_name = 'MAIN_MODULE_MEMBERSUBSCRIPTIONMASSACTION';
		$this->picto = 'members';
		$this->editor_name = 'iLLixM';
		$this->editor_url = 'https://github.com/iLLixM/dolibarr-member-subscription-massaction';
		$this->module_parts = array('hooks' => array('memberlist'), 'moduleforexternal' => 0);
		$this->depends = array('modAdherent');
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->langfiles = array('membersubscriptionmassaction@membersubscriptionmassaction');
		$this->phpmin = array(7, 4);
		$this->need_dolibarr_version = array(21, 0, 0);
		$this->dirs = array();
		$this->config_page_url = array();
		$this->const = array();
		// Reuse existing member and optional invoice permissions; no redundant module grant.
		$this->rights = array();
		$this->menu = array();
		$this->hidden = false;
	}

	/**
	 * Register only Dolibarr module metadata; there are no tables or business writes.
	 * @param string $options Activation options
	 * @return int
	 */
	public function init($options = '')
	{
		return $this->_init(array(), $options);
	}

	/**
	 * Remove only the module registration; retain all business objects.
	 * @param string $options Deactivation options
	 * @return int
	 */
	public function remove($options = '')
	{
		return $this->_remove(array(), $options);
	}
}
