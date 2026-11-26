<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
ob_start();
require __DIR__.'/booking.php';
$postBaseline = $passed;

class PostCommitBookingDouble
{
	public $calls = 0; public $invoice = true;
	public function book($id, $row, $options, $user, $overlap)
	{
		$this->calls++;
		$result = array('status' => 'success', 'message' => 'MSMBooked', 'id' => 9, 'amount' => 12,
			'mail' => 'not_selected', 'stop' => false, '_member' => (object) array('id' => $id));
		if ($this->invoice) {
			$result['invoice'] = array('id' => 42, 'ref' => 'INV-SYNTHETIC', 'entity' => 1, 'customer_id' => 4,
				'customer_created' => false, 'subscription_id' => 9, 'member_id' => $id, 'pdf_model' => 'crabe', 'pdf' => 'pending');
		}
		return $result;
	}
}
class PostCommitPdfDouble
{
	public $calls = 0; public $status = 'verified'; public $saves;
	public function generate($identity, $user)
	{
		$this->calls++;
		$last = end($this->saves);
		verify($last['results'][1]['invoice']['pdf']['status'] === 'unknown', 'unknown PDF checkpoint precedes generation');
		return array('status' => $this->status, 'message' => $this->status === 'verified' ? 'MSMPdfVerified' : 'MSMPdfGenerationFailed');
	}
}
class PostCommitMailDouble
{
	public $calls = 0; public $saves;
	public function send($member, $row, $user, $selected, $invoice = null)
	{
		$this->calls++;
		$last = end($this->saves);
		if ($invoice !== null) { verify($last['results'][1]['invoice']['pdf'] === $invoice['pdf'], 'PDF result checkpoint precedes mail'); }
		if (!$selected) { return 'not_selected'; }
		return $invoice !== null && $invoice['pdf']['status'] !== 'verified' ? 'attachment_missing' : 'transport';
	}
}
function postState($invoice, $mail)
{
	return array('phase' => 'processing', 'revision' => 2, 'ids' => array(1), 'overlap' => array(),
		'options' => array('invoice' => $invoice, 'autocreate' => false, 'sendmail' => $mail, 'description' => 'Synthetic'),
		'rows' => array(1 => array('errors' => array(), 'overlaps' => array())),
		'results' => array(1 => array('status' => 'unknown')));
}
foreach (array(array(true, true, 'verified', 'transport'), array(true, true, 'failed', 'attachment_missing'), array(true, false, 'verified', 'not_selected')) as $case) {
	$saves = array(); $service = new PostCommitBookingDouble(); $pdf = new PostCommitPdfDouble(); $mail = new PostCommitMailDouble();
	$pdf->status = $case[2]; $pdf->saves =& $saves; $mail->saves =& $saves;
	$done = msmProcessBooking(postState($case[0], $case[1]), $service, $mail, new PreviewUser(), static function ($state) use (&$saves) { $saves[] = $state; }, $pdf);
	verify($service->calls === 1 && $pdf->calls === 1 && $mail->calls === 1, 'one post-commit effect sequence');
	verify($done['results'][1]['status'] === 'success' && $done['results'][1]['invoice']['pdf']['status'] === $case[2]
		&& $done['results'][1]['mail'] === $case[3], 'business success retained across post-commit outcome');
}
$saves = array(); $service = new PostCommitBookingDouble(); $service->invoice = false;
$pdf = new PostCommitPdfDouble(); $pdf->saves =& $saves; $mail = new PostCommitMailDouble(); $mail->saves =& $saves;
$done = msmProcessBooking(postState(false, true), $service, $mail, new PreviewUser(), static function ($state) use (&$saves) { $saves[] = $state; }, $pdf);
verify($pdf->calls === 0 && $mail->calls === 1 && $done['results'][1]['mail'] === 'transport', 'subscription-only mail remains independent');
// Real module Booking + simulated MySQL observation, through production orchestration.
// No transport or files: these doubles fail if effects occur before confirmed completion.
foreach (array(false, true) as $ambiguous) {
	list($db, $service, $row) = bookingFixture(); $db->family = 'mysql'; $db->commitFail = $ambiguous;
	$state = postState(false, true); $state['rows'][1] = $row; $state['ids'] = array(1, 2);
	$state['rows'][2] = $row; $state['results'][2] = array('status' => 'unknown');
	$saves = array(); $pdf = new PostCommitPdfDouble(); $pdf->saves =& $saves;
	$mail = new PostCommitMailDouble(); $mail->saves =& $saves;
	$done = msmProcessBooking($state, $service, $mail, new PreviewUser(), static function ($updated) use (&$saves) { $saves[] = $updated; }, $pdf);
	verify($pdf->calls === 0 && $mail->calls === ($ambiguous ? 0 : 2), 'MySQL mail only after confirmed outer commit');
	verify($done['results'][1]['status'] === ($ambiguous ? 'unknown' : 'success'), 'MySQL orchestration preserves unknown result');
	if ($ambiguous) { verify($done['batch_stop'] && $done['results'][2]['status'] === 'not_processed' && $done['results'][2]['message'] === 'MSMBatchStopped' && count(array_keys($db->events, 'subscription', true)) === 1, 'C04-04/CPC-02 unknown stops batch without retry or postcommit effects'); }
}
echo ($passed - $postBaseline)." post-commit assertions passed (synthetic checkpoints; no files, database or SMTP).\n";

require_once __DIR__."/terminal.php";
