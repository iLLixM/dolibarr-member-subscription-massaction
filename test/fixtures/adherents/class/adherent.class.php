<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
class Adherent
{
	public $entity;
	public static $entities = array(1 => 1, 2 => 1, 3 => 2);
	public function __construct($db) {}
	public function fetch($id, $ref = '', $fk_soc = 0, $ref_ext = '', $optional = true, $subscriptions = true)
	{
		if ($optional || $subscriptions) { throw new RuntimeException('Unexpected broad fetch'); }
		$this->entity = self::$entities[$id] ?? 0;
		return $this->entity ? 1 : 0;
	}
}
