<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
class DolibarrModules
{
	protected function _init($sql, $options = '') { return $sql === array() ? 1 : -1; }
	protected function _remove($sql, $options = '') { return $sql === array() ? 1 : -1; }
}
