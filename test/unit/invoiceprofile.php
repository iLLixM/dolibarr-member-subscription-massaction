<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require __DIR__.'/preview.php';
require __DIR__.'/../../class/invoiceprofile.class.php';
$before = $passed;
class InvoiceProfileFixture extends MsmInvoiceProfile
{
	public $term = 1; public $rate = 0; public $item; public $useCoreVat = false;
	protected function paymentTerm() { return $this->term; }
	protected function paymentTermLabel($id) { return 'Synthetic term'; }
	protected function vat($product) { return $this->useCoreVat ? parent::vat($product) : $this->rate; }
	protected function product($id, $user) { return $this->item; }
	protected function models($number, $pdf) { parent::models($number, $pdf); }
}
$conf->global = new stdClass(); $conf->entity = 1; $conf->currency = 'EUR';
$conf->modules = array('member' => 1, 'invoice' => 1, 'societe' => 1);
$user = new PreviewUser(); $mysoc = (object) array('country_id' => 1);
$member = (object) array('entity' => 1, 'socid' => 0, 'thirdparty' => null);
$profile = new InvoiceProfileFixture(null);
foreach (array('', 'none', 'bankdirect', 'bankviainvoice', 'invoiceonly') as $option) {
	$conf->global->ADHERENT_BANK_USE = $option;
	verify(msmInvoiceDefault($user) === ($option === 'invoiceonly'), 'invoiceonly default without bank');
}
$user->denied = array('societe.lire'); verify(!msmInvoiceDefault($user), 'default respects customer read rights'); $user->denied = array();
$conf->global->ADHERENT_BANK_USE = 'invoiceonly'; $conf->global->ADHERENT_DEFAULT_SENDINFOBYMAIL = 1; $conf->global->MAIN_USE_ADVANCED_PERMS = 1;
$user->denied = array('facture.invoice_advance.send');
verify(msmInvoiceDefault($user), 'invoiceonly default independent from configured mail and advanced send right');
fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, true, true); }, 'NotEnoughPermissions');
verify($profile->resolve($member, $user, true, false)['term_label'] === 'Synthetic term', 'invoice without mail preserves confirmed payment term');
$user->denied = array(); unset($conf->global->ADHERENT_DEFAULT_SENDINFOBYMAIL, $conf->global->MAIN_USE_ADVANCED_PERMS);
$plan = $profile->resolve($member, $user, true, false);
verify($plan['quantity'] === 1 && $plan['price_base'] === 'TTC' && $plan['currency'] === 'EUR' && $plan['vat'] === 0.0 && $plan['term_label'] === 'Synthetic term', 'gross unit invoice plan with display metadata');
verify($plan['create_customer'] && $plan['customer_id'] === 0, 'explicit planned customer');
fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, false, false); }, 'SubscriptionBulkThirdPartyMissing');
$member->socid = 4;
fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, true, false); }, 'SubscriptionBulkThirdPartyMissing');
$member->thirdparty = (object) array('id' => 4, 'entity' => 1, 'multicurrency_code' => 'USD');
fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, false, false); }, 'MSMInvoiceProfileUnsupported');
$member->thirdparty->multicurrency_code = 'EUR';
$old = $profile->resolve($member, $user, false, false);
$member->thirdparty->name = 'Changed';
verify($old !== $profile->resolve($member, $user, false, false), 'customer changes invalidate profile');
$configured = $profile->resolve($member, $user, false, false); $conf->global->ADHERENT_BANK_USE = 'bankdirect';
verify($configured !== $profile->resolve($member, $user, false, false), 'invoice setting changes invalidate profile'); unset($conf->global->ADHERENT_BANK_USE);
$profile->term = 0;
fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, false, false); }, 'MSMInvoiceProfileUnsupported'); $profile->term = 1;
$profile->rate = '20*';
fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, false, false); }, 'MSMInvoiceProfileUnsupported'); $profile->rate = 0;
$conf->global->FACTURE_ADDON_PDF = '../crabe';
fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, false, false); }, 'MSMInvoiceProfileUnsupported'); unset($conf->global->FACTURE_ADDON_PDF);
$conf->global->ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS = 1; $profile->item = (object) array('packaging' => 2, 'ref' => 'P-<1>', 'label' => 'Synthetic & service');
fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, false, false); }, 'MSMInvoiceProfileUnsupported'); unset($conf->global->ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS);
foreach (array('processing', 'done') as $phase) {
	$old = array('format' => 3, 'phase' => $phase, 'revision' => 7, 'options' => array('invoice' => false));
	verify(msmUpgradePreview($old) === $old, 'consumed old state never reopened');
}
$old['phase'] = 'confirm'; $new = msmUpgradePreview($old);
verify($new['phase'] === 'preview' && $new['revision'] === 8 && !$new['options']['invoice'], 'old confirmation needs fresh consent');
verify(msmUpgradePreview($new) === $new, 'format migration idempotent');
$conf->global->SOCIETE_DISABLE_PROSPECTSCUSTOMERS = 0;
$initial = $profile->resolve($member, $user, false, false);
$conf->global->SOCIETE_DISABLE_PROSPECTSCUSTOMERS = 1;
$changed = $profile->resolve($member, $user, false, false);
verify($initial !== $changed && $changed['settings']['SOCIETE_DISABLE_PROSPECTSCUSTOMERS'] === '1', 'customer classification configuration invalidates confirmed profile');
// C1-02/03: no application bootstrap; actual profile and pinned Core VAT function.
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
$conf->global = new stdClass(); $profile->useCoreVat = true;
$conf->cache['country_code_in_EEC'] = array('DE'); // Synthetic country cache, no dictionary connection.
$mysoc = new Societe(new PreviewDatabase()); $mysoc->country_id = 1; $mysoc->country_code = 'DE';
$mysoc->tva_assuj = 0; $mysoc->tva_intra = ''; $mysoc->state_id = 0;
$hookmanager = new class { public function executeHooks(...$args) { return 0; } };
foreach (array('0', 'defaultforfoundationcountry', '7') as $setting) {
	$conf->global->ADHERENT_VAT_FOR_SUBSCRIPTIONS = $setting;
	if (DOL_VERSION === '24.0.3' && $setting === '7') {
		fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, false, false); }, 'MSMInvoiceVatIdUnsupported');
	} else {
		verify($profile->resolve($member, $user, false, false)['vat'] === 0.0, 'Core VAT policy '.DOL_VERSION.' '.$setting);
	}
}
// PR6-01: strict supported literals; malformed IDs must execute the real rejection.
foreach (array(null, '', '0', 'defaultforfoundationcountry') as $setting) {
	if ($setting === null) { unset($conf->global->ADHERENT_VAT_FOR_SUBSCRIPTIONS); }
	else { $conf->global->ADHERENT_VAT_FOR_SUBSCRIPTIONS = $setting; }
	verify($profile->resolve($member, $user, false, false)['vat'] === 0.0, 'PR6 supported VAT literal '.var_export($setting, true));
}
foreach (array('7', '7foo', ' 7bad', '+7bad', '7.5', '1e2', '-1', '00', '0.0', '0foo', ' 0', '0 ', ' ', 'garbage', 'DEFAULTFORFOUNDATIONCOUNTRY') as $setting) {
	$conf->global->ADHERENT_VAT_FOR_SUBSCRIPTIONS = $setting;
	if (DOL_VERSION === '24.0.3') {
		fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, false, false); }, 'MSMInvoiceVatIdUnsupported');
	} else {
		verify($profile->resolve($member, $user, false, false)['vat'] === 0.0, 'PR6 preserves V22 VAT literal '.$setting);
	}
}
// Both cores delegate to Core VAT; only V24 invokes the VAT hook (V22 returns zero early).
$conf->global->ADHERENT_VAT_FOR_SUBSCRIPTIONS = 'defaultforfoundationcountry';
$hookmanager = new class {
	public $resArray = array('vatvalue' => 19, 'vatrule' => 'Synthetic override');
	public function executeHooks(...$args) { return 1; }
};
verify($profile->resolve($member, $user, false, false)['vat'] === (DOL_VERSION === '24.0.3' ? 19.0 : 0.0), 'foundation-country preserves Core VAT result');
$conf->global->ADHERENT_VAT_FOR_SUBSCRIPTIONS = '0';
verify($profile->resolve($member, $user, false, false)['vat'] === 0.0, 'zero configuration does not call Core VAT');
$conf->global = new stdClass();
$expectedModel = DOL_VERSION === '24.0.3' ? 'sponge' : 'crabe';
verify($profile->resolve($member, $user, false, false)['pdf_model'] === $expectedModel, 'empty PDF configuration pinned fallback');
$conf->global->FACTURE_ADDON_PDF = 'sponge';
verify($profile->resolve($member, $user, false, false)['pdf_model'] === 'sponge', 'explicit PDF model precedes fallback');
$conf->global->FACTURE_ADDON_PDF_0 = 'sponge'; $conf->global->FACTURE_ADDON_PDF = 'missing_synthetic_model';
verify($profile->resolve($member, $user, false, false)['pdf_model'] === 'sponge', 'type PDF model precedes general model');
foreach (array('../sponge', 'missing_synthetic_model') as $model) {
	$conf->global->FACTURE_ADDON_PDF_0 = $model; $conf->global->FACTURE_ADDON_PDF = 'sponge';
	fails(static function () use ($profile, $member, $user) { $profile->resolve($member, $user, false, false); }, 'MSMInvoiceProfileUnsupported');
}
$conf->global = new stdClass();
class ConfiguredInvoicePreview extends PreviewFixtureService
{
	public $profile;
	protected function invoiceProfile() { return $this->profile; }
}
$db = new PreviewDatabase(); $synthetic = new PreviewMember();
$preview = new ConfiguredInvoicePreview($db, $synthetic); $preview->profile = $profile;
$preview->type = (object) array('duration' => '1y', 'label' => 'Synthetic type', 'amount' => '12', 'status' => 1, 'subscription' => 1);
$start = stamp('2030-01-01');
$conf->global->ADHERENT_VAT_FOR_SUBSCRIPTIONS = '0';
$confirmed = $preview->previewSubscriptionWithOptions($user, $start, true, false, null, true);
verify(!$confirmed['errors'] && isset($confirmed['invoice']), 'VAT original invoice preview valid');
foreach (array('defaultforfoundationcountry', '7') as $setting) {
	$conf->global->ADHERENT_VAT_FOR_SUBSCRIPTIONS = $setting;
	$changed = $preview->previewSubscriptionWithOptions($user, $start, true, false, null, true);
	verify($changed['fingerprint'] !== $confirmed['fingerprint'], 'VAT configuration invalidates confirmation '.$setting);
	if ($setting === '7') {
		verify(in_array('MSMInvoiceVatIdUnsupported', $changed['errors'], true) === (DOL_VERSION === '24.0.3'), 'VAT ID localized preflight error only on V24');
		$without = $preview->previewSubscriptionWithOptions($user, $start, false, false);
		verify(!$without['errors'] && !isset($without['invoice']), 'VAT ID does not block subscription-only preview');
	}
}

echo ($passed - $before)." invoice profile assertions passed (synthetic; no database).\n";
