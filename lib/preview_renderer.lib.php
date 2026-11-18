<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

/** Escaped module translations, including interpolation. */
function msmText($key, ...$values)
{
	global $langs;
	return dol_escape_htmltag($langs->transnoentities($key, ...$values));
}

/** ISO native date control, usable without JavaScript; no Today link. */
function msmDateField($name, $value, $label)
{
	return '<input type="date" min="1970-01-01" max="9998-12-31" style="min-width:10rem" name="'.dol_escape_htmltag($name).'" aria-label="'.dol_escape_htmltag($label).'" value="'.dol_escape_htmltag($value).'">';
}

/** Keep preview, confirmation, in-progress and completed result headings distinct. */
function msmPageTitleKey($phase)
{
	$keys = array('preview' => 'MSMTitle', 'confirm' => 'MSMConfirmTitle', 'processing' => 'MSMProcessingTitle', 'done' => 'MSMResultsTitle');
	return $keys[$phase] ?? 'MSMProcessingTitle';
}

/** Render only the server-confirmed invoice profile stored with the preview row. */
function msmInvoicePlanDetails(array $row)
{
	global $conf, $langs;
	$plan = $row['invoice'];
	$product = $langs->transnoentities('MSMInvoiceProductNone');
	if (!empty($plan['product'])) {
		$snapshot = $plan['product_snapshot'] ?? array();
		$product = trim((string) ($snapshot['ref'] ?? '').((string) ($snapshot['label'] ?? '') !== '' ? ' - '.$snapshot['label'] : ''));
		$product = ($product !== '' ? $product.' ' : '').'(#'.((int) $plan['product']).')';
	}
	$term = (string) ($plan['term_label'] ?? '');
	$term = ($term !== '' ? $term.' ' : '').'(#'.((int) $plan['term']).')';
	return msmText('MSMInvoicePlanDetails', price($row['amount']).' '.$conf->currency,
		dol_print_date(msmReadDate($plan['date']), 'day'), price($plan['vat']).'%', $product, $term);
}

/** Mirror V22 setAsCustomer without modifying the confirmed customer. */
function msmCustomerClassificationMayChange(array $row)
{
	$plan = $row['invoice'] ?? array();
	if (empty($plan['customer_id']) || !empty($plan['create_customer'])) { return false; }
	$client = (int) ($plan['customer_snapshot']['client'] ?? 0);
	$disableProspects = (int) ($plan['settings']['SOCIETE_DISABLE_PROSPECTSCUSTOMERS'] ?? 0);
	$target = (($client === 2 || $client === 3) && !$disableProspects) ? 3 : 1;
	return $client !== $target;
}

/** Warn when the confirmed Core setting may create a contact with a new third party. */
function msmInvoiceMayCreateContact(array $state)
{
	if (empty($state['options']['autocreate'])) { return false; }
	foreach ($state['rows'] as $row) {
		if (!empty($row['invoice']['create_customer']) && !empty($row['invoice']['settings']['THIRDPARTY_DEFAULT_CREATE_CONTACT'])) { return true; }
	}
	return false;
}

/** Pure rendering; dates/amounts/statuses come from the server-side services. */
function msmRenderPreview(array $state, $key)
{
	global $conf, $langs, $user;
	$html = '<div class="info" role="status">'.msmText('MSMPreviewNotice').'</div>';
	foreach (msmPreviewWarnings($state['rows']) as $warning) { $html .= '<div class="warning">'.msmText($warning).'</div>'; }
	$html .= '<form method="post"><input type="hidden" name="token" value="'.dol_escape_htmltag(newToken()).'">';
	$html .= '<input type="hidden" name="preview" value="'.dol_escape_htmltag($key).'"><input type="hidden" name="revision" value="'.$state['revision'].'">';
	$html .= '<fieldset><legend>'.msmText('MSMOptions').'</legend>';
	$html .= msmDateField('common', $state['common'], $langs->transnoentities('SubscriptionBulkCommonStart'));
	$html .= '<button class="button" name="action" value="common">'.msmText('SubscriptionBulkApplyCommon').'</button><br>';
	$invoiceAvailable = MsmPreviewService::canCreateSubscriptionWithOptions($user, true, false);
	foreach (array('sendmail' => 'MSMPlanMail', 'invoice' => 'MSMPlanInvoice', 'autocreate' => 'MSMPlanThirdParty') as $option => $label) {
		$disabled = (isset($state['profile']) && empty($state['profile']['features'][$option])) || ($option === 'invoice' && !$invoiceAvailable)
			|| ($option === 'autocreate' && (!$state['options']['invoice'] || !msmCanPlanThirdParty(true, $user)));
		$html .= '<label><input type="checkbox" name="'.$option.'" value="1"'.($state['options'][$option] && !$disabled ? ' checked' : '').($disabled ? ' disabled' : '').'> '.msmText($label).'</label><br>';
	}
	if (!$invoiceAvailable) { $html .= '<div class="info">'.msmText('MSMInvoiceUnavailable').'</div>'; }
	if ($state['options']['invoice'] && $state['options']['sendmail']) { $html .= '<div class="info">'.msmText('MSMInvoiceMailPdfRequired').'</div>'; }
	if (msmInvoiceMayCreateContact($state)) { $html .= '<div class="warning">'.msmText('MSMThirdPartyContactMayBeCreated').'</div>'; }
	$html .= '<label style="display:block">'.msmText('Label').' <input class="flat" style="display:block;width:100%;max-width:60rem;box-sizing:border-box" type="text" name="description" maxlength="1024" value="'.dol_escape_htmltag($state['options']['description']).'"></label>';
	$html .= '<p>'.msmText('SubscriptionBulkDescriptionHelp').'</p></fieldset>';
	$month = getDolGlobalInt('MEMBER_SUBSCRIPTION_SUGGEST_END_OF_MONTH');
	$year = getDolGlobalInt('MEMBER_SUBSCRIPTION_SUGGEST_END_OF_YEAR');
	if ($month || $year) {
		$html .= '<div class="info">'.msmText($month ? ($year ? 'SubscriptionBulkCalendarEndBothInfo' : 'SubscriptionBulkCalendarEndMonthInfo') : 'SubscriptionBulkCalendarEndYearInfo');
		$html .= '<br><button class="button" name="action" value="calendar">'.msmText('SubscriptionBulkApplyCalendar').'</button></div>';
	}
	$html .= '<p>'.msmText('SubscriptionBulkDateHelp').'</p><button class="button" name="action" value="refresh">'.msmText('SubscriptionBulkRefresh').'</button>';
	$html .= '<div class="div-table-responsive" style="width:0;min-width:100%"><table class="noborder centpercent"><thead><tr class="liste_titre">';
	foreach (array('Member', 'Type', 'Amount', 'DateSubscription', 'DateEndSubscription', 'ThirdParty', 'Status', 'MSMDetails') as $label) { $html .= '<th>'.msmText($label).'</th>'; }
	$html .= '</tr></thead><tbody>';
	$warnings = 0; $sum = 0.0;
	foreach ($state['rows'] as $id => $row) {
		$units = $row['quantity'] == 1 ? array('d' => 'Day', 'w' => 'Week', 'm' => 'Month', 'y' => 'Year') : array('d' => 'Days', 'w' => 'Weeks', 'm' => 'Months', 'y' => 'Years');
		$name = ($row['member'] ?: $langs->transnoentities('Member')).' #'.$id;
		$html .= '<tr class="oddeven" data-member="'.$id.'"><td>'.dol_escape_htmltag($name).'</td><td>'.dol_escape_htmltag($row['type']);
		if ($row['quantity']) { $html .= ' ('.dol_escape_htmltag($row['quantity']).' '.msmText($units[$row['unit']] ?? $row['unit']).')'; }
		$html .= '</td><td class="nowrap">'.($row['amount'] === null ? '' : dol_escape_htmltag(price($row['amount']).' '.$conf->currency)).'</td>';
		foreach (array('start', 'end') as $field) {
			$html .= '<td class="nowrap">'.msmDateField($field.'['.$id.']', $row[$field] > 0 ? dol_print_date($row[$field], '%Y-%m-%d') : '', $name.' '.$langs->transnoentities($field === 'start' ? 'DateSubscription' : 'DateEndSubscription'));
			if ($field === 'end') { $html .= ' <button class="button small" style="min-width:0;width:1.8rem;padding:.2rem;margin:0" name="action" value="reset:'.$id.'" aria-label="'.dol_escape_htmltag($langs->transnoentities('SubscriptionBulkRecalculateEnd').' '.$name).'">&#8635;</button>'; }
			$html .= '</td>';
		}
		$html .= '<td>'.dol_escape_htmltag($row['thirdparty']).'</td><td>'.msmText($row['status'] === 'error' ? 'SubscriptionBulkError' : ($row['status'] === 'warning' ? 'SubscriptionBulkWarning' : 'SubscriptionBulkReady')).'</td><td>';
		foreach ($row['notices'] as $notice) { $html .= '<div>'.msmText($notice).'</div>'; }
		if (!empty($row['invoice'])) {
			if (!empty($state['options']['invoice']) && msmCustomerClassificationMayChange($row)) { $html .= '<div class="warning">'.msmText('MSMCustomerClassificationMayChange').'</div>'; }
			$html .= '<div>'.msmInvoicePlanDetails($row).'</div>';
		}
		if ($row['quantity'] == 1 && $row['unit'] === 'd' && $row['end'] > 0) { $html .= '<div>'.msmText('MSMOneDay', dol_print_date($row['end'], 'day')).'</div>'; }
		$html .= '<div>'.dol_escape_htmltag($row['description']).'</div></td></tr>';
		if ($row['warnings']) { $warnings++; }
		if (!$row['errors'] && $row['amount'] !== null) { $sum += $row['amount']; }
	}
	$counts = msmPreviewCounts($state['rows']);
	$html .= '</tbody></table></div><p role="status">'.msmText('MSMCounts', (string) $counts['selected'], (string) $counts['ready'], (string) $counts['skipped'], (string) $warnings, price($sum).' '.$conf->currency).'</p>';
	$html .= '<button class="button" name="action" value="refresh">'.msmText('SubscriptionBulkRefresh').'</button> ';
	$html .= '<button class="button" name="action" value="confirm">'.msmText('MSMReviewBooking').'</button> ';
	$html .= '</form>';
	return $html.'<p><a href="'.dol_escape_htmltag(msmListUrl()).'">'.msmText('MSMBack').'</a></p>';
}
