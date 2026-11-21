<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
/** Admission and capability contracts are not runtime qualification. */
class MsmCoreCompatibility
{
	public static function profile($version)
	{
		if (!is_string($version) || !preg_match('/^[0-9]+\.[0-9]+\.[0-9]+(?:[-+][a-zA-Z0-9.-]+)?$/D', $version)
			|| version_compare($version, '21.0.0', '<')) { throw new RuntimeException('MSMCoreVersionUnsupported'); }
		// Frozen P1 evidence catalog, NOT an admission allowlist. New releases use
		// the observer automatically; this catalog needs no release-by-release updates.
		$verified = array(
			'22.0.0' => false, '22.0.1' => false, '22.0.2' => false,
			'22.0.3' => false, '22.0.4' => false, '22.0.5' => false,
			'23.0.0' => false, '23.0.1' => false, '23.0.2' => false,
			'23.0.3' => false, '23.0.4' => false,
			'24.0.0' => false, '24.0.1' => false, '24.0.2' => true, '24.0.3' => true,
		);
		return array('latest_end' => $verified[$version] ?? null);
	}
	public static function current()
	{
		return self::profile(defined('DOL_VERSION') ? DOL_VERSION : '');
	}
	public static function defaultPdf($root = null)
	{
		$root = $root ?? DOL_DOCUMENT_ROOT;
		foreach (array('crabe', 'sponge') as $model) {
			if (is_readable($root.'/core/modules/facture/doc/pdf_'.$model.'.modules.php')) { return $model; }
		}
		throw new RuntimeException('MSMCoreContractUnsupported');
	}
	/** Check call shape without invoking a Core business operation. */
	public static function methods($object, array $calls)
	{
		foreach ($calls as $name => $arguments) {
			if (!method_exists($object, $name)) { throw new RuntimeException('MSMCoreContractUnsupported'); }
			$m = new ReflectionMethod($object, $name);
			if (!$m->isPublic() || $m->isStatic() || $m->isAbstract() || $m->getNumberOfRequiredParameters() > $arguments
				|| (!$m->isVariadic() && $m->getNumberOfParameters() < $arguments)) { throw new RuntimeException('MSMCoreContractUnsupported'); }
		}
	}
	public static function load($file, $class, array $calls)
	{
		if (!class_exists($class, false)) {
			if (!is_readable(DOL_DOCUMENT_ROOT.'/'.$file)) { throw new RuntimeException('MSMCoreContractUnsupported'); }
			require_once DOL_DOCUMENT_ROOT.'/'.$file;
		}
		if (!class_exists($class, false)) { throw new RuntimeException('MSMCoreContractUnsupported'); }
		self::methods($class, $calls);
	}
	/** Reject incompatible inheritance before PHP declares the extension class. */
	public static function extension($class, $method, $arguments, array $references = array(), array $implemented = array())
	{
		$c = new ReflectionClass($class);
		if ($c->isFinal()) { throw new RuntimeException('MSMCoreContractUnsupported'); }
		$implemented[$method] = array($arguments, $references);
		foreach ($c->getMethods(ReflectionMethod::IS_ABSTRACT) as $required) {
			if (!isset($implemented[$required->getName()])) { throw new RuntimeException('MSMCoreContractUnsupported'); }
		}
		foreach ($implemented as $name => $contract) {
			self::extensionMethod($c, $name, $contract[0], $contract[1]);
		}
	}
	private static function extensionMethod($c, $method, $arguments, array $references)
	{
		if (!$c->hasMethod($method)) { return; }
		$m = $c->getMethod($method);
		if ($m->isFinal() || $m->isStatic() || $m->hasReturnType() || $m->returnsReference()
			|| $m->getNumberOfRequiredParameters() > $arguments || $m->getNumberOfParameters() > $arguments || $m->isVariadic()) {
			throw new RuntimeException('MSMCoreContractUnsupported');
		}
		foreach ($m->getParameters() as $i => $parameter) {
			if ($parameter->isPassedByReference() !== in_array($i, $references, true)) { throw new RuntimeException('MSMCoreContractUnsupported'); }
		}
	}
	public static function booking($db)
	{
		self::current();
		self::methods($db, array('begin' => 0, 'commit' => 0, 'rollback' => 0, 'query' => 1, 'prefix' => 0,
			'escape' => 1, 'idate' => 1, 'plimit' => 1, 'fetch_object' => 1, 'free' => 1));
		self::load('adherents/class/adherent.class.php', 'Adherent', array('subscription' => 10, 'fetch' => 6, 'update_end_date' => 1));
		self::extension('Adherent', 'update_end_date', 1);
	}
	public static function invoice()
	{
		self::current();
		self::load('compta/facture/class/facture.class.php', 'Facture', array('create' => 1, 'addline' => 16, 'validate' => 1, 'fetch' => 1, 'generateDocument' => 2));
		self::load('societe/class/societe.class.php', 'Societe', array('create_from_member' => 3, 'fetch' => 1));
		self::load('compta/facture/class/paymentterm.class.php', 'PaymentTerm', array('getDefaultId' => 0, 'fetch' => 1));
		if (!function_exists('get_default_tva')) { throw new RuntimeException('MSMCoreContractUnsupported'); }
	}
}
