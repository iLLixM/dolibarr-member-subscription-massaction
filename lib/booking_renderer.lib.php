<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
function msmRenderBooking(array $state, $key, array $access = array())
{
	global $conf;
	$confirm = $state['phase'] === 'confirm';
	$html = '<div class="info">'.msmText($confirm ? 'MSMConfirmNotice' : 'MSMResultNotice').'</div>';
	if (!empty($state['profile']['profile'])) { $html .= '<p>'.msmText('MSMProfileUsed', $state['profile']['profile']).'</p>'; }
	if (!$confirm && $state['phase'] !== 'done') { $html .= '<div class="warning">'.msmText('MSMInterrupted').'</div>'; }
	if ($confirm) {
		$html .= '<form method="post" id="msm-confirm-form"><input type="hidden" name="token" value="'.dol_escape_htmltag(newToken()).'"><input type="hidden" name="preview" value="'.dol_escape_htmltag($key).'"><input type="hidden" name="revision" value="'.$state['revision'].'">';
		$html .= '<style>table[data-msm-options="1"]{border-collapse:collapse;border:0;box-shadow:none}table[data-msm-options="1"] tr > th,table[data-msm-options="1"] tr > td{text-align:left;font-weight:normal;padding:.2em .75em .2em 0;border:0}</style>';
		$html .= '<table data-msm-options="1" style="width:auto;max-width:100%;margin:0 0 1rem">';
		foreach (array('sendmail' => 'MSMPlanMail', 'invoice' => 'MSMPlanInvoice', 'autocreate' => 'MSMPlanThirdParty') as $option => $label) {
			$html .= '<tr><th scope="row">'.msmText($label).'</th><td>'.msmText($state['options'][$option] ? 'Yes' : 'No').'</td></tr>';
		}
		$html .= '</table>';
		if ($state['options']['invoice'] && $state['options']['sendmail']) { $html .= '<p class="info">'.msmText('MSMInvoiceMailPdfRequired').'</p>'; }
		if (msmInvoiceMayCreateContact($state)) { $html .= '<p class="warning">'.msmText('MSMThirdPartyContactMayBeCreated').'</p>'; }
	}
	$html .= '<div class="div-table-responsive" style="width:0;min-width:100%"><table class="noborder centpercent"><tr class="liste_titre">';
	foreach (array('Member', 'Type', 'Amount', 'DateSubscription', 'DateEndSubscription', 'Label', 'Status') as $label) { $html .= '<th>'.msmText($label).'</th>'; }
	$html .= '</tr>';
	$counts = array('success' => 0, 'not_processed' => 0, 'skipped' => 0, 'failed' => 0, 'unknown' => 0, 'transport' => 0, 'hook' => 0, 'mailwarning' => 0, 'invoice' => 0, 'pdf' => 0, 'pdfwarning' => 0, 'customer' => 0); $sum = 0;
	foreach ($state['rows'] as $id => $row) {
		$html .= '<tr><td>'.dol_escape_htmltag($row['member']).'</td><td>'.dol_escape_htmltag($row['type']).'</td><td>'.dol_escape_htmltag(price($row['amount']).' '.$conf->currency).'</td>';
		foreach (array('start', 'end') as $field) { $html .= '<td>'.dol_escape_htmltag($row[$field] > 0 ? dol_print_date($row[$field], 'day') : '').'</td>'; }
		$html .= '<td>'.dol_escape_htmltag($row['description']).'</td><td>';
		if ($confirm) {
			foreach ($row['notices'] as $notice) { $html .= '<div>'.msmText($notice).'</div>'; }
			if (!empty($row['invoice'])) {
				if (!empty($state['options']['invoice']) && msmCustomerClassificationMayChange($row)) { $html .= '<div class="warning">'.msmText('MSMCustomerClassificationMayChange').'</div>'; }
				$html .= '<div>'.msmInvoicePlanDetails($row).'</div>';
			}
			if ($row['quantity'] == 1 && $row['unit'] === 'd') { $html .= '<div>'.msmText('MSMOneDay', dol_print_date($row['end'], 'day')).'</div>'; }
			foreach ($row['overlaps'] as $overlap) { $html .= '<div>#'.((int) $overlap['id']).' '.dol_escape_htmltag($overlap['start'].' – '.$overlap['end']).'</div>'; }
			if (!$row['errors']) { $counts['success']++; $sum += $row['amount']; }
			else { $counts['skipped']++; }
			if ($row['overlaps'] && !$row['errors']) { $html .= '<label><input type="checkbox" name="overlap['.$id.']" value="1"> '.msmText('MSMConfirmOverlap').'</label>'; }
		} else {
			$result = $state['results'][$id]; $counts[$result['status']]++;
			$html .= msmText('MSMStatus_'.$result['status']).': '.msmText($result['message']);
			if ($result['status'] === 'success') {
				$sum += $result['amount'];
				$html .= '<div><a href="'.dol_escape_htmltag(dol_buildpath('/adherents/subscription.php', 1).'?rowid='.$id).'">'.msmText('Subscription').' #'.((int) $result['id']).'</a></div>';
				if (!empty($result['invoice'])) {
					$counts['invoice']++;
					$invoiceLabel = msmText('Invoice').' '.dol_escape_htmltag($result['invoice']['ref']).' (#'.((int) $result['invoice']['id']).')';
					if (!empty($access[$id]['invoice'])) { $invoiceLabel = '<a href="'.dol_escape_htmltag(dol_buildpath('/compta/facture/card.php', 1).'?facid='.((int) $result['invoice']['id'])).'">'.$invoiceLabel.'</a>'; }
					$html .= '<div>'.$invoiceLabel.'</div>';
					if (!empty($result['invoice']['customer_created'])) {
						$counts['customer']++;
						$customerLabel = msmText('MSMCreatedThirdParty').' #'.((int) $result['invoice']['customer_id']);
						if (!empty($access[$id]['customer'])) { $customerLabel = '<a href="'.dol_escape_htmltag(dol_buildpath('/societe/card.php', 1).'?socid='.((int) $result['invoice']['customer_id'])).'">'.$customerLabel.'</a>'; }
						$html .= '<div>'.$customerLabel.'</div>';
					}
					$pdf = $result['invoice']['pdf'] ?? null;
					if (!is_array($pdf)) { $pdf = array('status' => 'unknown', 'message' => 'MSMPdfInterrupted'); }
					$html .= '<div>'.msmText('MSMPdfStatus').': '.msmText($pdf['message']).'</div>';
					if ($pdf['status'] === 'verified') { $counts['pdf']++; } else { $counts['pdfwarning']++; }
				}
				if (isset($result['attachment_status'])) {
					$attachment = $result['attachment_status'];
					if (!in_array($attachment, array('verified_for_transport', 'verified_before_hook', 'missing', 'not_sent', 'not_selected', 'not_required', 'not_processed', 'unknown'), true)) { $attachment = 'unknown'; }
					$html .= '<div>'.msmText('MSMAttachmentStatus').': '.msmText('MSMAttachment_'.$attachment).'</div>';
				}
				$html .= '<div>'.msmText('MSMMail_'.$result['mail']).'</div>';
				if (in_array($result['mail'], array('transport', 'hook'), true)) { $counts[$result['mail']]++; }
				elseif (!in_array($result['mail'], array('not_selected', 'not_processed'), true)) { $counts['mailwarning']++; }
			} elseif (!empty($result['diagnostic'])) {
				$html .= '<div>'.msmText('MSMRollbackStatus').': '.msmText(($result['rollback'] ?? '') === 'confirmed' ? 'MSMRollbackConfirmed' : 'MSMRollbackUnconfirmed').'</div>';
				$html .= '<div>'.msmText('MSMDiagnosticStage').': '.dol_escape_htmltag($result['diagnostic']['stage']).'</div>';
				foreach (array('subscription_id', 'invoice_id', 'customer_id') as $field) {
					if (!empty($result['diagnostic'][$field])) { $html .= '<div>'.dol_escape_htmltag($field).': #'.((int) $result['diagnostic'][$field]).' ('.msmText('MSMUnconfirmedIdentifier').')</div>'; }
				}
			}
		}
		$html .= '</td></tr>';
	}
	$html .= '</table></div><p>'.msmText($confirm ? 'MSMConfirmTotals' : 'MSMResultTotals', $counts['success'], $counts['skipped'], $counts['failed'], $counts['unknown'], price($sum).' '.$conf->currency).'</p>';
	if (!$confirm) {
		$html .= '<p>'.msmText('MSMNotProcessedTotal', $counts['not_processed']).'</p>';
		if (!empty($state['batch_stop'])) { $html .= '<div class="warning">'.msmText('MSMTerminalBatchStop').'</div>'; }
		if (!empty($state['checkpoint_failed'])) { $html .= '<div class="error">'.msmText('MSMSessionFailure').'</div>'; }
		$html .= '<p>'.msmText('MSMInvoiceTotals', $counts['invoice'], $counts['customer'], $counts['pdf'], $counts['pdfwarning']).'</p>';
		$html .= '<p>'.msmText('MSMMailTotals', $counts['transport'], $counts['hook'], $counts['mailwarning']).'</p>';
	}
	if ($confirm) {
		$html .= '<div id="msm-submit-status" class="info" role="status" aria-live="polite" hidden>'.msmText('MSMProcessingNotice').'</div>';
		$html .= '<button class="button" id="msm-final-submit" name="action" value="book"'.(!empty($state['profile_notice']) ? ' disabled' : '').'>'.msmText('MSMBook').'</button> <button class="button" name="action" value="edit">'.msmText('MSMEdit').'</button></form>';
	}
	return $html.'<p><a href="'.dol_escape_htmltag(msmListUrl()).'">'.msmText('MSMBack').'</a></p>';
}
