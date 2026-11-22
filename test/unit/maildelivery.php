<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
if (PHP_SAPI !== 'cli') { exit; }
// No application bootstrap, credentials, database or SMTP. Exercise the real adapter body.
define('DOL_DOCUMENT_ROOT', __DIR__.'/../fixtures');
require __DIR__.'/../../class/mail.class.php';
$passed = 0;
function checkMail($ok, $label) { global $passed; if (!$ok) { throw new RuntimeException($label); } $passed++; }
function getDolGlobalString($key) { return $key === 'ADHERENT_EMAIL_TEMPLATE_SUBSCRIPTION' ? 'Synthetic template' : ''; }
function getDolGlobalInt($key) { return 0; }
function isValidEmail($s) { return strpos($s, '@') !== false; }
function dol_escape_htmltag($s) { return htmlspecialchars($s, ENT_QUOTES); }
function dol_print_date($t, ...$args) { return gmdate('Y-m-d', $t); }
function dol_textishtml($s, $mode) { return strpos($s, '<p>') !== false; }
function price($v) { return (string) $v; }
function getCommonSubstitutionArray(...$args) { return array('__REF__' => 'SYNTHETIC'); }
function complete_substitutions_array(&$values, ...$args) { $values['__EXTRA__'] = 'extra'; }
function make_substitutions($text, $values, ...$args) { return strtr($text, $values); }
function dol_concatdesc($a, $b) { return $a."\n".$b; }
class Translate { public $defaultlang; public function __construct(...$args) {} public function setDefaultLang($s) { $this->defaultlang = $s; } public function loadLangs($v) {} }
class MsmPreviewService { public static function canCreateSubscriptionWithOptions($user) { return true; } }
class Adherent
{
	public $id = 1; public $entity = 1; public $typeid = 1; public $email = 'synthetic@example.invalid'; public $default_lang = 'de_DE'; public $invoice;
	public $sent; public $returnCode = 1;
	public function sendEmail(...$args) { $GLOBALS['last_mail'] = $args; $GLOBALS['mail_language'] = $GLOBALS['langs']->defaultlang; return $this->returnCode; }
}
class DeliveryHooks { public function executeHooks($name, $params, &$object, &$action) { return 0; } }
class DeliveryAdapter extends MsmSubscriptionMail {
	protected function permitted($user) { return true; }
	protected function hooks() { return new DeliveryHooks(); }
	public function inspectEffectPhase($member, $row, $user, $invoice = null) {
		$started = false;
		$status = $this->sendInternal($member, $row, $user, true, $invoice, $started);
		return array($status, $started);
	}
}
class InvoiceAttachmentDouble
{
	public $calls = 0; public $fail = false;
	public function attachment($identity, $descriptor, $user)
	{
		$this->calls++;
		if ($this->fail) { throw new RuntimeException('changed'); }
		return array('_path' => '/isolated/INV-SYNTHETIC.pdf', 'name' => 'INV-SYNTHETIC.pdf');
	}
}
class InvoiceDeliveryHooks
{
	public $code = 0; public $calls = 0; public $fail = false;
	public function executeHooks($name, $params, &$object, &$action) {
		$this->calls++;
		if ($this->fail) { throw new RuntimeException('Synthetic hook uncertainty'); }
		return $this->code;
	}
}
class InvoiceDeliveryAdapter extends DeliveryAdapter
{
	public $pdfAdapter; public $hookAdapter;
	protected function invoicePermitted($user) { return true; }
	protected function pdf() { return $this->pdfAdapter; }
	protected function hooks() { return $this->hookAdapter; }
}
$conf = new stdClass(); $mysoc = (object) array('default_lang' => 'en_US'); $langs = (object) array('original' => true); $original = $langs; $hookmanager = new stdClass();
$member = new Adherent(); $member->invoice = (object) array('ref' => 'DO-NOT-ATTACH');
$row = array('description' => '<unsafe>', 'start' => 1791590400, 'end' => 1823040000, 'amount' => 99);
$mail = new DeliveryAdapter(new stdClass());
checkMail($mail->send($member, $row, new stdClass(), true) === 'transport', 'adapter transport success');
checkMail($last_mail[2] === array() && $last_mail[3] === array() && $last_mail[4] === array(), 'invoice attachments excluded');
checkMail(strpos($last_mail[0], '&lt;unsafe&gt;') !== false && strpos($last_mail[0], 'SYNTHETIC') !== false && strpos($last_mail[0], 'Type text') !== false, 'description standard substitutions and type text');
checkMail($last_mail[1] === 'Member SYNTHETIC extra' && $last_mail[8] === 1, 'subject substitution and core format detection');
checkMail($mail_language === 'de_DE' && $langs === $original, 'member language and global restoration');
checkMail($GLOBALS['template_request'][0] === 'member' && $GLOBALS['template_request'][1] === 'Synthetic template', 'standard FormMail template selection');
$plain_template = true;
checkMail($mail->send($member, $row, new stdClass(), true) === 'transport' && $last_mail[8] === 0 && strpos($last_mail[0], '<unsafe>') !== false, 'plain description preserved without changing template format');
$member->returnCode = -1; checkMail($mail->send($member, $row, new stdClass(), true) === 'failed', 'adapter propagates transport failure');
$member->returnCode = 1; $invoiceMail = new InvoiceDeliveryAdapter(new stdClass());
$invoiceMail->pdfAdapter = new InvoiceAttachmentDouble(); $invoiceMail->hookAdapter = new InvoiceDeliveryHooks();
$invoice = array('id' => 42, 'pdf' => array('status' => 'verified', 'sha256' => 'synthetic'));
checkMail($invoiceMail->send($member, $row, new stdClass(), true, $invoice) === 'transport', 'invoice mail standard transport');
checkMail($last_mail[2] === array('/isolated/INV-SYNTHETIC.pdf') && $last_mail[3] === array('application/pdf') && $last_mail[4] === array('INV-SYNTHETIC.pdf'), 'verified invoice PDF attached');
checkMail($invoiceMail->pdfAdapter->calls === 2 && $invoiceMail->hookAdapter->calls === 1, 'invoice PDF verified before hook and again before standard transport');
$invoice['pdf']['status'] = 'failed';
checkMail($invoiceMail->send($member, $row, new stdClass(), true, $invoice) === 'attachment_missing', 'failed PDF blocks standard transport');
checkMail($invoiceMail->hookAdapter->calls === 1, 'missing PDF blocks hook dispatch');
$invoice['pdf']['status'] = 'verified'; $invoiceMail->pdfAdapter->fail = true;
checkMail($invoiceMail->send($member, $row, new stdClass(), true, $invoice) === 'attachment_missing', 'changed PDF blocks standard transport');
checkMail($invoiceMail->hookAdapter->calls === 1, 'changed PDF blocks hook dispatch');
$invoiceMail->pdfAdapter->fail = false;
$invoiceMail->hookAdapter->code = 1;
checkMail($invoiceMail->send($member, $row, new stdClass(), true, $invoice) === 'hook', 'positive hook remains distinct from standard invoice transport');
checkMail($invoiceMail->pdfAdapter->calls === 4 && $invoiceMail->hookAdapter->calls === 2, 'verified PDF required before hook takeover without second transport resolution');
// Phase 4: the real adapter reports verified attachment evidence separately
// from transport/hook outcome; it never equates either with delivered mail.
$invoiceMail->hookAdapter->code = 0;
$detail = $invoiceMail->send($member, $row, new stdClass(), true, $invoice);
checkMail($detail === 'transport',
	'F38-03 legacy invoice send retains its return code under existing B/C use');
$invoiceMail->hookAdapter->code = 1;
$detail = $invoiceMail->send($member, $row, new stdClass(), true, $invoice);
checkMail($detail === 'hook',
	'F38-03 legacy hook handling unchanged while guarded evidence requires capability');
$invoiceMail->hookAdapter->code = 0;
$invoiceMail->pdfAdapter->fail = true;
$detail = $invoiceMail->send($member, $row, new stdClass(), true, $invoice);
checkMail($detail === 'attachment_missing',
	'F38-03 legacy verified PDF mismatch blocks hook as before');
$invoiceMail->pdfAdapter->fail = false;
$invoiceMail->hookAdapter->fail = true;
$detail = $invoiceMail->send($member, $row, new stdClass(), true, $invoice);
checkMail($detail === 'unknown',
	'F38-02 legacy hook exception continues to return unknown');
$invoiceMail->hookAdapter->fail = false;
$invoiceMail->hookAdapter->code = -1;
$negativeHook = $invoiceMail->inspectEffectPhase($member, $row, new stdClass(), $invoice);
checkMail($negativeHook === array('failed', true),
	'F38-02 negative hook return is after entering potentially effectful hook');
$invoiceMail->hookAdapter->code = 0;
$member->returnCode = -1;
$negativeSmtp = $invoiceMail->inspectEffectPhase($member, $row, new stdClass(), $invoice);
checkMail($negativeSmtp === array('failed', true),
	'F38-02 negative Core SMTP return is after effectful hook/transport boundary');
$member->returnCode = 1;
// F38-03: direct service without original file-session capability fails closed.
$previousCalls = $invoiceMail->hookAdapter->calls;
$detail = $invoiceMail->sendWithEvidence($member, $row, new stdClass(), true, $invoice);
checkMail($detail['mail'] === 'denied' && $detail['attachment'] === 'not_sent'
	&& $invoiceMail->hookAdapter->calls === $previousCalls,
	'F38-03 no direct invoice evidence send without original checkpoint');
$detail = $mail->sendWithEvidence($member, $row, new stdClass(), true);
checkMail($detail['mail'] === 'denied' && $detail['attachment'] === 'not_required',
	'F38-03 direct contribution-only evidence cannot bypass original capability');
checkMail($mail->send($member, $row, new stdClass(), true) === 'transport',
	'F38-03 legacy B/C send() unchanged while direct evidence is denied');
// F38-05: finite mail/attachment result matrix is enforced before checkpointing.
require_once __DIR__.'/../../lib/booking.lib.php';
$validInvoice = array(
	'transport'=>'verified_for_transport',
	'hook'=>'verified_before_hook',
	'attachment_missing'=>'missing',
	'unknown'=>'unknown',
	'failed'=>'not_sent',
	'denied'=>'not_sent',
	'missing'=>'not_sent',
	'disabled'=>'not_sent',
);
$allAttachment=array('verified_for_transport','verified_before_hook','missing','not_sent','unknown','not_required');
foreach ($validInvoice as $status => $expectedAttachment) {
	foreach ($allAttachment as $candidate) {
		$actual = msmValidMailAttachmentEvidence(array('mail'=>$status,'attachment'=>$candidate),true);
		checkMail($actual === ($candidate === $expectedAttachment),
			'F38-05 invoice evidence pair '.$status.'/'.$candidate);
	}
}
foreach (array('transport','hook','failed','denied','unknown','missing','disabled','attachment_missing') as $status) {
	foreach ($allAttachment as $candidate) {
		checkMail(msmValidMailAttachmentEvidence(array('mail'=>$status,'attachment'=>$candidate),false)
			=== ($candidate === 'not_required'),
			'F38-05 contribution evidence pair '.$status.'/'.$candidate);
	}
}
checkMail(!msmValidMailAttachmentEvidence(array('mail'=>'transport','attachment'=>'verified_before_hook'),true)
	&& !msmValidMailAttachmentEvidence(array('mail'=>'failed','attachment'=>'verified_for_transport'),true)
	&& !msmValidMailAttachmentEvidence(array('mail'=>'hook','attachment'=>'verified_for_transport'),true),
	'F38-05 contradictory adapter evidence pairs never redeem successful effect');
echo $passed." mail adapter assertions passed (template/type/transport doubles; no SMTP).\n";
