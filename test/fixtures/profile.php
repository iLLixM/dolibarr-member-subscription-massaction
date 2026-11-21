<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require_once __DIR__.'/../../class/transactionprofile.class.php';
/** Legacy business fixtures supply the server context now supplied by the controller.
 * Only configuration storage is doubled; real profile admission remains in use.
 */
class FixtureTransactionProfile extends MsmTransactionProfile
{
	private $model;
	public function __construct($db, $observer, $entity) { $this->model = $db; parent::__construct($db, $observer, $entity); }
	public static function raw($db)
	{
		if (property_exists($db, 'profileRaw')) { return $db->profileRaw; }
		return $db->family === 'mariadb' ? null : json_encode(array('version' => 1, 'family' => 'mysql', 'profile' => 'EnhancedMySQL', 'nonce' => str_repeat('a', 32)));
	}
	protected function read() { return self::raw($this->model); }
	public static function context($db, $user)
	{
		global $conf;
		$raw = self::raw($db); $c = $raw === null ? array() : json_decode($raw, true);
		$name = $c['profile'] ?? ($db->family === 'mariadb' ? 'MariaDBSnapshot' : '');
		$enabled = in_array($name, array('CoreGuarded', 'EnhancedMySQL', 'MariaDBSnapshot'), true);
		$extended = $enabled && $name !== 'CoreGuarded';
		return array('family' => $db->family, 'profile' => $name, 'revision' => hash('sha256', $raw === null ? 'missing' : 'configured:'.$raw),
			'features' => array('booking' => $enabled, 'invoice' => $extended, 'autocreate' => $extended, 'sendmail' => $extended), 'owner' => (int) $user->id, 'entity' => (int) $conf->entity, 'confirmation_revision' => 0);
	}
}
trait ProfileBookingFixture
{
	protected $profileDb;
	public $autoBindProfile = true;
	protected function profiles() { global $conf; return new FixtureTransactionProfile($this->profileDb, $this->databaseState, (int) $conf->entity); }
	public function book($id, array $expected, array $options, $user, $overlapConfirmed, $checkpoint = null)
	{
		if ($this->autoBindProfile && !isset($expected['_profile'])) { $expected['_profile'] = FixtureTransactionProfile::context($this->profileDb, $user); }
		return parent::book($id, $expected, $options, $user, $overlapConfirmed, $checkpoint);
	}
}
