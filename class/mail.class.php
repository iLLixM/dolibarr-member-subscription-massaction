<?php
/* Copyright (C) 2026 iLLixM
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
		if ($current->fetch($user->id) <= 0 || empty($current->status)) { return false; }
		$current->loadRights('', 1);
		return MsmPreviewService::canCreateSubscriptionWithOptions($current);
	}
	protected function invoicePermitted($user)
	{
		$current = new User($this->db);
		if ($current->fetch($user->id) <= 0 || empty($current->status)) { return false; }
		$current->loadRights('', 1);
		return MsmPreviewService::canCreateSubscriptionWithOptions($current, true, true);
	}
	protected function pdf() { require_once __DIR__.'/invoicepdf.class.php'; return new MsmInvoicePdfService($this->db); }
	protected function hooks()
	{
		require_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
		$manager = new HookManager($this->db); $manager->initHooks(array('subscription'));
		return $manager;
	}
	protected function deliver($member, array $row, $user, &$isolatedSmtpFailure = null)
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
		// Preserve member sender, tracking, attachment and Core recipient semantics.
		$attachment = $row['_attachment'] ?? null;
		$paths = $attachment === null ? array() : array($attachment['_path']);
		$mimes = $attachment === null ? array() : array('application/pdf');
		$names = $attachment === null ? array() : array($attachment['name']);
		$header = "X-Dolibarr-Info: member subscription module\r\n";
		if ($isolatedSmtpFailure !== null && $this->canObserveStandardSmtp()) {
			// Use the same Core constructor arguments and member substitutions as
			// Adherent::sendEmail, and retain its SMTP object as the outcome witness.
			require_once DOL_DOCUMENT_ROOT.'/core/class/CMailFile.class.php';
			$text = $member->makeSubstitution($message);
			$subject = $member->makeSubstitution($subject);
			$from = getDolGlobalString('ADHERENT_MAIL_FROM', $conf->email_from);
			$mail = new CMailFile($subject, (string) $member->email, $from,
				$ishtml ? dol_htmlentitiesbr($text) : $text, $paths, $mimes, $names,
				'', '', 0, $ishtml, '', '', 'mem'.$member->id, $header);
			return $this->sendObservedSmtp($mail, $isolatedSmtpFailure);
		}
		$result = $member->sendEmail($message, $subject, $paths, $mimes, $names, '', '', 0, $ishtml, '', $header);
		return $result > 0 ? 'transport' : 'failed';
	}
	/** Give configured mail extensions their usual path; their failures remain unqualified. */
	private function canObserveStandardSmtp()
	{
		global $conf, $hookmanager;
		$mode = getDolGlobalString('MAIN_MAIL_SENDMODE_STANDARD');
		$specific = $mode !== '' && $mode !== 'default';
		if (!$specific) { $mode = getDolGlobalString('MAIN_MAIL_SENDMODE'); }
		if ($mode !== 'smtps' || getDolGlobalString('MAIN_MAIL_SMTPS_AUTH_TYPE'.($specific ? '_STANDARD' : '')) === 'XOAUTH2'
			|| !is_object($hookmanager) || get_class($hookmanager) !== 'HookManager' || !empty($hookmanager->hooks)) {
			return false;
		}
		foreach (($conf->modules_parts['hooks'] ?? array()) as $module => $contexts) {
			$contexts = is_array($contexts) ? $contexts : explode(':', $contexts);
			if (isModEnabled($module) && array_intersect($contexts, array('all', 'subscription', 'mail'))) { return false; }
		}
		return true;
	}
	protected function sendObservedSmtp($mail, &$isolatedSmtpFailure)
	{
		$smtp = $mail->smtps;
		$observable = $mail->sendmode === 'smtps' && is_object($smtp) && get_class($smtp) === 'SMTPs'
			&& $smtp->getErrors() === '' && empty($mail->error);
		if ($mail->sendfile()) { return 'transport'; }
		// Only a new error from the same Core SMTP instance, propagated by
		// CMailFile, proves entry into this isolated transport boundary.
		$isolatedSmtpFailure = $observable && $mail->smtps === $smtp && $this->canObserveStandardSmtp()
			&& $smtp->getErrors() !== '' && $mail->error === $smtp->getErrors();
		return 'failed';
	}
	/** Only the original consumed transaction's one-shot checkpoint may
	 * authorize a direct, standalone CoreGuarded evidence service call.
	 * This factory is overridable by named isolated synthetic fixtures only.
	 */
	protected function evidenceProfile()
	{
		global $conf;
		require_once __DIR__.'/transactionprofile.class.php';
		return new MsmTransactionProfile($this->db, new MsmDatabaseState($this->db), (int) $conf->entity);
	}
	/** Legacy B/C send() deliberately does NOT acquire a new admission gate.
	 * Direct evidence callers must provide the original post-COMMIT capability,
	 * whose durable readback has already succeeded for this specific effect.
	 */
	public function sendWithEvidence($member, array $row, $user, $selected, array $invoice = null, $checkpoint = null, $id = null)
	{
		if (!$selected) { return array('mail' => 'not_selected', 'attachment' => $invoice === null ? 'not_required' : 'not_selected'); }
		try {
			if (!($checkpoint instanceof MsmFileBookingCheckpoint)
				|| !is_int($id) || $id < 1 || (int) ($member->id ?? 0) !== $id
				|| !is_object($user) || (int) ($user->id ?? 0) < 1) {
				throw new RuntimeException('MSMCoreGuardedSessionUnsupported');
			}
			$profile = $this->evidenceProfile()->current();
			if (($profile['profile'] ?? null) !== 'CoreGuarded'
				|| empty($profile['features']['sendmail'])
				|| ($invoice !== null && empty($profile['features']['invoice']))) {
				throw new RuntimeException('MSMProfileFeatureUnavailable');
			}
			$checkpoint->requireMailEvidence($id, $user, $invoice, $profile, $member, $row);
		} catch (Throwable $e) {
			// Denied/uncertain admission must never reach a hook or SMTP.
			return array('mail' => 'denied', 'attachment' => $invoice === null ? 'not_required' : 'not_sent', 'continuation' => 'blocked');
		}
		// Preserve legacy send() return values while treating negative results
		// after entering any hook or SMTP call as potentially effected.
		$afterEffect = false;
		$isolatedSmtpFailure = false;
		$status = $this->sendInternal($member, $row, $user, $selected, $invoice, $afterEffect, $isolatedSmtpFailure);
		if ($afterEffect && !in_array($status, array('transport', 'hook'), true)) {
			$status = 'unknown';
		}
		$attachment = 'not_required';
		if ($invoice !== null) {
			$attachment = 'not_sent';
			if ($status === 'transport') { $attachment = 'verified_for_transport'; }
			elseif ($status === 'hook') { $attachment = 'verified_before_hook'; }
			elseif ($status === 'attachment_missing') { $attachment = 'missing'; }
			elseif ($status === 'unknown') { $attachment = 'unknown'; }
		}
		$delivery = array('mail' => $status, 'attachment' => $attachment);
		if ($status === 'unknown' && $isolatedSmtpFailure) { $delivery['continuation'] = 'isolated_smtp_failure'; }
		return $delivery;
	}
	public function send($member, array $row, $user, $selected, array $invoice = null)
	{
		$afterEffect = false;
		return $this->sendInternal($member, $row, $user, $selected, $invoice, $afterEffect);
	}
	protected function sendInternal($member, array $row, $user, $selected, $invoice, &$afterEffect, &$isolatedSmtpFailure = null)
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
			$afterEffect = true; // A hook can do external work before returning < 0.
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
			return $this->deliver($object, $row, $user, $isolatedSmtpFailure);
		} catch (Throwable $e) { return 'unknown'; }
		finally { $hookmanager = $previous; $langs = $previousLangs; }
	}
}
