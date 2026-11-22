<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
/**
 * CF05-06 bounded standard-Core invoice/FS qualification.
 *
 * This is a fail-closed support boundary, not a claim about arbitrary
 * third-party triggers or custom-patched Dolibarr cores. No database query,
 * transaction operation, file mutation or HTTP/admin override.
 */
class MsmCoreFinanceQualification
{
	private static function invoiceBlobs()
	{
		// Verified against the exact pinned full-CI Dolibarr Git blobs.
		return array(
			'22.0.0'=>'e6be36884916182c90f645eae602642d54656780',
			'22.0.1'=>'e6be36884916182c90f645eae602642d54656780',
			'22.0.2'=>'fdbb5ab31bd73a604e6e5b5fc76c2b5ae21a07ed',
			'22.0.3'=>'2c49e88f5199428f3f8727c69153a9e872385bc4',
			'22.0.4'=>'ecb5ebe6897c01b85712e3a204d5b55aaf67accc',
			'22.0.5'=>'b0687548ac6fb52ac4150ba1a2c6fa5b6d5314f9',
			'23.0.0'=>'934dc84214ca5dd9f0fd25c4cb02f0d8a5d4df26',
			'23.0.1'=>'8896c01417742843ef1f9a638c87c13ccf8c5b78',
			'23.0.2'=>'0ebb0b7627ab10295462d3bf3f5491d92519d5d4',
			'23.0.3'=>'c8fdda0d2e3761302f06e3e7c4c96b18f91dd2e2',
			'23.0.4'=>'b051a68760027703176662e96dd9d00bd0ab62d3',
			'24.0.0'=>'7bf55537b565a89ccb4a84868e72db2c6960f506',
			'24.0.1'=>'1cd9b00dad58d67b683cd205f7d7db8f962da83b',
			'24.0.2'=>'31f972e772cb35bd27ad7962aff4cb96e81e10ee',
			'24.0.3'=>'f019dff8a7389282cf549c0a4ad9c9a665865fcd'
		);
	}
	/** Git blob identity, not raw SHA1: matches the immutable pinned Core tree. */
	public static function sourceAllowed($version, $gitBlobSha)
	{
		$known = self::invoiceBlobs();
		return is_string($version) && is_string($gitBlobSha)
			&& isset($known[$version]) && hash_equals($known[$version], $gitBlobSha);
	}
	public static function requireSource($version, $source)
	{
		if (!is_string($source) || !self::sourceAllowed($version,
			sha1('blob '.strlen($source)."\0".$source))) {
			throw new RuntimeException('MSMCoreFinanceUnqualified');
		}
		// The pinned source has a conditional pre-COMMIT PROV rename:
		// path absence must ALSO be verified immediately before validate().
	}
	/** The exact enabled trigger selection mimics Core Interfaces::run_triggers.
	 * Only standard Agenda and inactive Logevents are qualified here.
	 * Any additional active Core or external trigger is outside the supported
	 * CoreGuarded finance configuration until separately source-reviewed.
	 */
	public static function requireTriggerPaths($coreDir, array $extraDirs)
	{
		global $conf;
		$dirs = array($coreDir);
		foreach ($extraDirs as $rel) {
			if (!is_string($rel) || $rel === '' || !function_exists('dol_buildpath')) {
				throw new RuntimeException('MSMCoreFinanceUnqualified');
			}
			$dirs[] = dol_buildpath($rel, 0);
		}
		foreach ($dirs as $index => $dir) {
			if (!is_dir($dir)) {
				if ($index !== 0) { continue; } // Core skips absent optional directories.
				throw new RuntimeException('MSMCoreFinanceUnqualified');
			}
			$entries = scandir($dir);
			if (!is_array($entries)) { throw new RuntimeException('MSMCoreFinanceUnqualified'); }
			foreach ($entries as $file) {
				if (!preg_match('/^interface_[0-9]+_([^_]+)_(.+)\\.class\\.php$/i', $file, $matches)) { continue; }
				if (!is_readable($dir.'/'.$file)) { throw new RuntimeException('MSMCoreFinanceUnqualified'); }
				if (preg_match('/NORUN$/i', $file)) { continue; }
				$module = strtolower(preg_replace('/^mod/i', '', $matches[1]));
				if ($module !== 'all' && (!function_exists('isModEnabled') || !isModEnabled($module))) { continue; }
				if ($index !== 0 || !in_array($module, array('agenda','all'), true)) {
					throw new RuntimeException('MSMCoreFinanceUnqualified');
				}
			}
		}
		if (function_exists('isModEnabled') && isModEnabled('agenda')
			&& getDolGlobalInt('MAIN_COPY_FILE_IN_EVENT_AUTO')) {
			throw new RuntimeException('MSMCoreFinanceUnqualified');
		}
		foreach (array('MEMBER_SUBSCRIPTION_CREATE','COMPANY_CREATE','CONTACT_CREATE',
			'BILL_CREATE','LINEBILL_INSERT','BILL_VALIDATE') as $event) {
			if (!getDolGlobalInt('MAIN_LOGEVENTS_DISABLE_ALL')
				&& getDolGlobalString('MAIN_LOGEVENTS_'.$event)) {
				throw new RuntimeException('MSMCoreFinanceUnqualified');
			}
		}
	}
	/** Call BEFORE any Booking BEGIN and again at the direct Invoice boundary. */
	public static function requireReady()
	{
		global $conf;
		if (!defined('DOL_DOCUMENT_ROOT') || !defined('DOL_VERSION')) { throw new RuntimeException('MSMCoreFinanceUnqualified'); }
		$path = DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
		if (!is_file($path) || is_link($path) || !is_readable($path)) { throw new RuntimeException('MSMCoreFinanceUnqualified'); }
		$source = file_get_contents($path);
		self::requireSource(DOL_VERSION, $source);
		$extra = $conf->modules_parts['triggers'] ?? array();
		if (!is_array($extra)) { throw new RuntimeException('MSMCoreFinanceUnqualified'); }
		self::requireTriggerPaths(DOL_DOCUMENT_ROOT.'/core/triggers', $extra);
		if (!is_object($conf->facture ?? null) || !is_string($conf->facture->dir_output ?? null)
			|| $conf->facture->dir_output === '') { throw new RuntimeException('MSMCoreFinanceUnqualified'); }
	}
	/** After create + addline, BEFORE validate() and any BILL_VALIDATE trigger.
	 * A pre-existing PROV directory would be renamed by Core before outer COMMIT.
	 * The trigger admission restricts future in-validate directory creation;
	 * this check by itself would NOT protect arbitrary custom triggers.
	 */
	public static function requireProvisionalPathSafe($invoice)
	{
		global $conf;
		if (!is_object($invoice) || (int) ($invoice->id ?? 0) <= 0
			|| !is_string($invoice->ref ?? null)
			|| !preg_match('/^\\(?PROV[0-9]+\\)?$/iD', $invoice->ref)
			|| !function_exists('dol_sanitizeFileName')) { throw new RuntimeException('MSMCoreFinanceUnqualified'); }
		$root = $conf->facture->dir_output ?? null;
		if (!is_string($root) || $root === '') { throw new RuntimeException('MSMCoreFinanceUnqualified'); }
		$path = rtrim($root, '/').'/'.dol_sanitizeFileName($invoice->ref);
		if (file_exists($path) || is_link($path)) { throw new RuntimeException('MSMCoreFinanceUnqualified'); }
	}
}
