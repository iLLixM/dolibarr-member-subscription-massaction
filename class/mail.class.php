<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
/** Subscription mail adapter, called only for a confirmed commit. */
class MsmSubscriptionMail
{
	protected $db;
	public function __construct($db) { $this->db = $db; }
	protected function permitted($user)
	{
		$current = new User($this->db);
		if ($current->fetch($user->id) <= 0 || empty($current->statut)) { return false; }
		$current->getrights('', 1);
		return MsmPreviewService::canCreateSubscriptionWithOptions($current);
	}
	protected function invoicePermitted($user)
	{
		$current = new User($this->db);
		if ($current->fetch($user->id) <= 0 || empty($current->statut)) { return false; }
		$current->getrights('', 1);
		return MsmPreviewService::canCreateSubscriptionWithOptions($current, true, true);
	}
	protected function pdf() { require_once __DIR__.'/invoicepdf.class.php'; return new MsmInvoicePdfService($this->db); }
	protected function hooks()
	{
		require_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
		$manager = new HookManager($this->db); $manager->initHooks(array('subscription'));
		return $manager;
	}
	protected function deliver($member, array $row, $user)
	{
		global $conf, $langs, $mysoc;
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
		require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent_type.class.php';
		$type = new AdherentType($this->db);
		if ($type->fetch($member->typeid) <= 0) { return 'failed'; }
		$outputlangs = new Translate('', $conf);
		$outputlangs->setDefaultLang(!empty($member->default_lang) ? $member->default_lang : (!empty($member->thirdparty->default_lang) ? $member->thirdparty->default_lang : $mysoc->default_lang));
		$outputlangs->loadLangs(array('main', 'members'));
		$langs = $outputlangs;
		$formmail = new FormMail($this->db);
		$template = null; $label = getDolGlobalString('ADHERENT_EMAIL_TEMPLATE_SUBSCRIPTION');
		if ($label !== '') { $template = $formmail->getEMailTemplate($this->db, 'member', $user, $outputlangs, 0, 1, $label); }
		$subject = is_object($template) && $template->id > 0 ? (string) $template->topic : '';
		$message = is_object($template) && $template->id > 0 ? (string) $template->content : '';
		$message = dol_concatdesc($message, $type->getMailOnSubscription());
		$ishtml = dol_textishtml($message, 0) ? 1 : 0;
		$substitutions = getCommonSubstitutionArray($outputlangs, 0, null, $member);
		complete_substitutions_array($substitutions, $outputlangs, $member);
		// Explicit subscription tokens supplement the existing member/common tokens.
		$substitutions['__SUBSCRIPTION_DESCRIPTION__'] = $ishtml ? dol_escape_htmltag($row['description']) : $row['description'];
		$substitutions['__SUBSCRIPTION_START__'] = dol_print_date($row['start'], 'day', false, $outputlangs);
		$substitutions['__SUBSCRIPTION_END__'] = dol_print_date($row['end'], 'day', false, $outputlangs);
		$substitutions['__SUBSCRIPTION_AMOUNT__'] = price($row['amount']);
		$subject = make_substitutions($subject, $substitutions, $outputlangs);
		$message = make_substitutions($message, $substitutions, $outputlangs);
		if (trim($message) === '') { return 'failed'; }
		// Adherent::sendEmail keeps the Core sender, tracking and recipient semantics.
		$attachment = $row['_attachment'] ?? null;
		$paths = $attachment === null ? array() : array($attachment['_path']);
		$mimes = $attachment === null ? array() : array('application/pdf');
		$names = $attachment === null ? array() : array($attachment['name']);
		$result = $member->sendEmail($message, $subject, $paths, $mimes, $names, '', '', 0, $ishtml, '', "X-Dolibarr-Info: member subscription module\r\n");
		return $result > 0 ? 'transport' : 'failed';
	}
	public function send($member, array $row, $user, $selected, array $invoice = null)
	{
		global $hookmanager, $langs;
		if (!$selected) { return 'not_selected'; }
		try {
			if (!$this->permitted($user) || ($invoice !== null && !$this->invoicePermitted($user))) { return 'denied'; }
		} catch (Throwable $e) { return 'unknown'; }
		if (getDolGlobalInt('MAIN_DISABLE_ALL_MAILS')) { return 'disabled'; }
		if (!isValidEmail((string) $member->email)) { return 'missing'; }
		$attachment = null;
		if ($invoice !== null) {
			if (($invoice['pdf']['status'] ?? '') !== 'verified') { return 'attachment_missing'; }
			try { $attachment = $this->pdf()->attachment($invoice, $invoice['pdf'], $user); }
			catch (Throwable $e) { return 'attachment_missing'; }
		}
		$previous = $hookmanager; $previousLangs = $langs;
		try {
			$object = clone $member; $action = 'subscription';
			$hookmanager = $this->hooks();
			$parameters = array('datesubscription' => $row['start'], 'amount' => $row['amount'], 'accountid' => 0, 'ccountid' => 0, 'operation' => '', 'label' => $row['description'], 'num_chq' => '', 'emetteur_nom' => '', 'emetteur_banque' => '', 'datesubend' => $row['end'], 'invoice' => $invoice);
			$result = $hookmanager->executeHooks('sendMail', $parameters, $object, $action);
			if ($result < 0) { return 'failed'; }
			if ($result > 0) { return 'hook'; }
			// Respect a valid replacement, never silently change the confirmed recipient.
			if (!($object instanceof Adherent) || (int) $object->id !== (int) $member->id || (int) $object->entity !== (int) $member->entity || $object->email !== $member->email || (int) $object->typeid !== (int) $member->typeid) { return 'failed'; }
			if (getDolGlobalInt('MAIN_DISABLE_ALL_MAILS')) { return 'disabled'; }
			if ($invoice !== null) {
				// A hook may have changed the file after the first mandatory verification.
				try { $attachment = $this->pdf()->attachment($invoice, $invoice['pdf'], $user); }
				catch (Throwable $e) { return 'attachment_missing'; }
			}
			if ($attachment !== null) { $row['_attachment'] = $attachment; }
			return $this->deliver($object, $row, $user);
		} catch (Throwable $e) { return 'unknown'; }
		finally { $hookmanager = $previous; $langs = $previousLangs; }
	}
}
