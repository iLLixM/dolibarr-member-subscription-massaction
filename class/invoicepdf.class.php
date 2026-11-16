<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

/** Generate and resolve one confirmed invoice PDF after the business commit. */
class MsmInvoicePdfService
{
	protected $db;
	public function __construct($db) { $this->db = $db; }

	protected function invoice()
	{
		require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
		return new Facture($this->db);
	}

	protected function currentUser($user)
	{
		$current = new User($this->db);
		if ($current->fetch($user->id) <= 0 || empty($current->status)) { throw new RuntimeException('NotEnoughPermissions'); }
		$current->loadRights('', 1);
		return $current;
	}

	protected function entityRoot($entity)
	{
		global $conf;
		return $conf->invoice->multidir_output[$entity] ?? '';
	}

	protected function generateCore($invoice, $model, $outputlangs)
	{
		return $invoice->generateDocument($model, $outputlangs);
	}

	protected function load(array $identity, $user)
	{
		$invoice = $this->invoice();
		if ($invoice->fetch((int) $identity['id']) <= 0
			|| (int) $invoice->entity !== (int) $identity['entity']
			|| (int) $invoice->socid !== (int) $identity['customer_id']
			|| (string) $invoice->ref !== (string) $identity['ref']
			|| (int) $invoice->status !== 1
			|| !in_array((int) $invoice->entity, array_map('intval', explode(',', getEntity('invoice'))), true)
			|| !checkUserAccessToObject($user, array('facture'), $invoice)) {
			throw new RuntimeException('NotEnoughPermissions');
		}
		$res = $this->db->query("SELECT COUNT(*) AS n FROM ".$this->db->prefix()."element_element WHERE sourcetype = 'subscription' AND targettype = 'facture' AND fk_source = ".((int) $identity['subscription_id']).' AND fk_target = '.((int) $identity['id']));
		$link = $res ? $this->db->fetch_object($res) : null;
		if ($res) { $this->db->free($res); }
		if (!$link || (int) $link->n !== 1) { throw new RuntimeException('MSMPdfIdentityMismatch'); }
		return $invoice;
	}

	/** Resolve the only acceptable filename from trusted object identity and entity root. */
	protected function expected(array $identity)
	{
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		$root = $this->entityRoot((int) $identity['entity']);
		$root = $root !== '' ? realpath($root) : false;
		$ref = dol_sanitizeFileName((string) $identity['ref']);
		if ($root === false || $ref === '' || $ref === '.' || $ref === '..') { throw new RuntimeException('MSMPdfUnsafePath'); }
		$directory = $root.'/'.$ref;
		if (is_link($directory)) { throw new RuntimeException('MSMPdfUnsafePath'); }
		if (is_dir($directory)) {
			$resolvedDirectory = realpath($directory);
			if ($resolvedDirectory === false || $resolvedDirectory !== $directory || strpos($resolvedDirectory, $root.'/') !== 0) {
				throw new RuntimeException('MSMPdfUnsafePath');
			}
		}
		return array($root, $directory.'/'.$ref.'.pdf', $ref.'.pdf');
	}

	protected function verifyFile(array $identity, $invoice, array $descriptor = null)
	{
		list($root, $path, $name) = $this->expected($identity);
		clearstatcache(true, $path);
		$resolved = realpath($path);
		if ($resolved === false || strpos($resolved, $root.'/') !== 0 || is_link($path) || !is_file($resolved) || !is_readable($resolved)) {
			throw new RuntimeException('MSMPdfUnsafePath');
		}
		$handle = fopen($resolved, 'rb');
		$signature = $handle ? fread($handle, 5) : false;
		if ($handle) { fclose($handle); }
		if ($signature !== '%PDF-') { throw new RuntimeException('MSMPdfInvalid'); }
		if (class_exists('finfo')) {
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			if ($finfo->file($resolved) !== 'application/pdf') { throw new RuntimeException('MSMPdfInvalid'); }
		}
		$last = str_replace('\\', '/', (string) ($invoice->last_main_doc ?? ''));
		$ref = substr($name, 0, -4);
		if ($last === '' || substr('/'.$last, -strlen('/'.$ref.'/'.$name)) !== '/'.$ref.'/'.$name || strpos($last, '../') !== false) {
			throw new RuntimeException('MSMPdfMetadataMismatch');
		}
		$stat = stat($resolved);
		$result = array('status' => 'verified', 'message' => 'MSMPdfVerified', 'name' => $name,
			'size' => (int) $stat['size'], 'mtime' => (int) $stat['mtime'], 'sha256' => hash_file('sha256', $resolved));
		if ($descriptor !== null && ($descriptor['status'] ?? '') !== 'verified') { throw new RuntimeException('MSMPdfInvalid'); }
		foreach (array('name', 'size', 'mtime', 'sha256') as $field) {
			if ($descriptor !== null && ($descriptor[$field] ?? null) !== $result[$field]) { throw new RuntimeException('MSMPdfChanged'); }
		}
		$result['_path'] = $resolved;
		return $result;
	}

	public function generate(array $identity, $user)
	{
		try {
			$user = $this->currentUser($user);
			if (!MsmPreviewService::canCreateSubscriptionWithOptions($user, true, false)) { throw new RuntimeException('NotEnoughPermissions'); }
			$invoice = $this->load($identity, $user);
			list(, $path) = $this->expected($identity);
			clearstatcache(true, $path);
			if (file_exists($path) || is_link($path)) { throw new RuntimeException('MSMPdfStale'); }
			global $conf, $mysoc;
			$outputlangs = new Translate('', $conf);
			$defaultlang = !empty($invoice->thirdparty->default_lang) ? $invoice->thirdparty->default_lang
				: (!empty($mysoc->default_lang) ? $mysoc->default_lang : getDolGlobalString('MAIN_LANG_DEFAULT', 'en_US'));
			$outputlangs->setDefaultLang($defaultlang);
			$model = (string) $identity['pdf_model'];
			if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/D', $model) || $this->generateCore($invoice, $model, $outputlangs) <= 0) {
				throw new RuntimeException('MSMPdfGenerationFailed');
			}
			$invoice = $this->load($identity, $user);
			$result = $this->verifyFile($identity, $invoice);
			unset($result['_path']);
			return $result;
		} catch (Throwable $e) {
			$key = in_array($e->getMessage(), array('NotEnoughPermissions', 'MSMPdfStale', 'MSMPdfUnsafePath', 'MSMPdfInvalid', 'MSMPdfMetadataMismatch', 'MSMPdfIdentityMismatch', 'MSMPdfGenerationFailed'), true) ? $e->getMessage() : 'MSMPdfUnknown';
			return array('status' => $key === 'MSMPdfUnknown' ? 'unknown' : 'failed', 'message' => $key);
		}
	}

	/** Re-resolve and re-hash just before transport; the returned path is never persisted. */
	public function attachment(array $identity, array $descriptor, $user)
	{
		$user = $this->currentUser($user);
		if (!MsmPreviewService::canCreateSubscriptionWithOptions($user, true, true)) { throw new RuntimeException('NotEnoughPermissions'); }
		$invoice = $this->load($identity, $user);
		return $this->verifyFile($identity, $invoice, $descriptor);
	}
}
