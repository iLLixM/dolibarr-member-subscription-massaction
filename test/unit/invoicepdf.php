<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */
require __DIR__.'/booking.php';
require __DIR__.'/../../class/invoicepdf.class.php';
$pdfBaseline = $passed;

class PdfInvoiceDouble
{
	public $id = 42; public $entity = 1; public $socid = 4; public $ref = 'INV-SYNTHETIC';
	public $status = 1; public $last_main_doc = '';
}
class PdfServiceDouble extends MsmInvoicePdfService
{
	public $root; public $object; public $mode = '';
	protected function currentUser($user) { return $user; }
	protected function entityRoot($entity) { return $this->root; }
	protected function load(array $identity, $user)
	{
		if ((int) $this->object->entity !== (int) $identity['entity'] || (int) $this->object->socid !== (int) $identity['customer_id']
			|| $this->object->ref !== $identity['ref'] || $this->object->status !== 1) { throw new RuntimeException('NotEnoughPermissions'); }
		return $this->object;
	}
	protected function generateCore($invoice, $model, $outputlangs)
	{
		if ($this->mode === 'negative') { return -1; }
		if ($this->mode === 'exception') { throw new RuntimeException('synthetic'); }
		$dir = $this->root.'/'.$invoice->ref;
		if (!is_dir($dir)) { mkdir($dir, 0700); }
		$path = $dir.'/'.$invoice->ref.'.pdf';
		if ($this->mode === 'symlink') { symlink('/etc/passwd', $path); }
		elseif ($this->mode !== 'missing') { file_put_contents($path, $this->mode === 'invalid' ? 'not-pdf' : "%PDF-1.4\nsynthetic\n"); }
		$invoice->last_main_doc = $this->mode === 'metadata' ? 'facture/OTHER/OTHER.pdf' : 'facture/'.$invoice->ref.'/'.$invoice->ref.'.pdf';
		return 1;
	}
}
function pdfFixture()
{
	$root = sys_get_temp_dir().'/msm-pdf-'.bin2hex(random_bytes(8));
	mkdir($root, 0700);
	$service = new PdfServiceDouble(new stdClass()); $service->root = $root; $service->object = new PdfInvoiceDouble();
	$identity = array('id' => 42, 'entity' => 1, 'customer_id' => 4, 'ref' => 'INV-SYNTHETIC', 'subscription_id' => 9, 'pdf_model' => 'crabe');
	return array($root, $service, $identity);
}
function pdfCleanup($root)
{
	$dir = $root.'/INV-SYNTHETIC'; $file = $dir.'/INV-SYNTHETIC.pdf';
	if (is_link($file) || is_file($file)) { unlink($file); }
	if (is_dir($dir)) { rmdir($dir); }
	if (is_dir($root)) { rmdir($root); }
}
function pdfSymlinkDirectoryCase($outside, $attachment)
{
	list($root, $pdf, $identity) = pdfFixture();
	$target = $outside ? sys_get_temp_dir().'/msm-pdf-target-'.bin2hex(random_bytes(8)) : $root.'/target';
	mkdir($target, 0700);
	$targetFile = $target.'/INV-SYNTHETIC.pdf';
	file_put_contents($targetFile, "%PDF-1.4\nsymlink-target\n");
	symlink($target, $root.'/INV-SYNTHETIC');
	if ($attachment) {
		$pdf->object->last_main_doc = 'facture/INV-SYNTHETIC/INV-SYNTHETIC.pdf';
		$stat = stat($targetFile);
		$descriptor = array('status' => 'verified', 'message' => 'MSMPdfVerified', 'name' => 'INV-SYNTHETIC.pdf',
			'size' => $stat['size'], 'mtime' => $stat['mtime'], 'sha256' => hash_file('sha256', $targetFile));
		fails(static function () use ($pdf, $identity, $descriptor) { $pdf->attachment($identity, $descriptor, new PreviewUser()); }, 'MSMPdfUnsafePath');
	} else {
		verify($pdf->generate($identity, new PreviewUser())['message'] === 'MSMPdfUnsafePath', 'symlink invoice directory blocks generation '.($outside ? 'outside' : 'inside').' root');
	}
	unlink($root.'/INV-SYNTHETIC'); unlink($targetFile); rmdir($target); rmdir($root);
}
$user = new PreviewUser();
list($root, $pdf, $identity) = pdfFixture();
$verified = $pdf->generate($identity, $user);
verify($verified['status'] === 'verified' && $verified['name'] === 'INV-SYNTHETIC.pdf' && $verified['size'] > 5, 'verified generated PDF descriptor');
$attachment = $pdf->attachment($identity, $verified, $user);
verify($attachment['sha256'] === $verified['sha256'] && strpos($attachment['_path'], $root.'/') === 0, 'attachment re-resolved below entity root');
file_put_contents($attachment['_path'], "%PDF-1.4\nchanged\n");
fails(static function () use ($pdf, $identity, $verified, $user) { $pdf->attachment($identity, $verified, $user); }, 'MSMPdfChanged');
pdfCleanup($root);

foreach (array('negative' => 'unknown', 'exception' => 'unknown', 'missing' => 'unknown', 'invalid' => 'unknown', 'symlink' => 'unknown', 'metadata' => 'unknown') as $mode => $status) {
	list($root, $pdf, $identity) = pdfFixture(); $pdf->mode = $mode;
	$result = $pdf->generate($identity, $user);
	verify($result['status'] === $status, 'PDF outcome '.$mode);
	pdfCleanup($root);
}
list($root, $pdf, $identity) = pdfFixture();
mkdir($root.'/INV-SYNTHETIC', 0700); file_put_contents($root.'/INV-SYNTHETIC/INV-SYNTHETIC.pdf', "%PDF-1.4\nstale\n");
verify($pdf->generate($identity, $user)['message'] === 'MSMPdfStale', 'preexisting artifact rejected'); pdfCleanup($root);
list($root, $pdf, $identity) = pdfFixture(); $pdf->object->entity = 2;
verify($pdf->generate($identity, $user)['message'] === 'NotEnoughPermissions', 'wrong invoice entity rejected'); pdfCleanup($root);
list($root, $pdf, $identity) = pdfFixture(); $identity['pdf_model'] = '../unsafe';
verify($pdf->generate($identity, $user)['status'] === 'failed'
	&& $pdf->generate($identity, $user)['message'] === 'MSMPdfGenerationFailed',
	'unsafe model denied before starting generator with deterministic failure'); pdfCleanup($root);
foreach (array(false, true) as $outside) {
	pdfSymlinkDirectoryCase($outside, false);
	pdfSymlinkDirectoryCase($outside, true);
}
echo ($passed - $pdfBaseline)." invoice PDF assertions passed (isolated temporary files; no Dolibarr documents).\n";
