<?php
/**
 * Module Web_Module_Administrative_Document_Invoice_Pay
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Document_Invoice_Pay extends Module {

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = 'administrative/document/invoice/pay.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();
		$pager = new Pager('document');
		$pager->add_condition('classname', 'Document_Incoming_Invoice');
		$pager->add_join('document_incoming_invoice', 'document_id', 'document.id');
		$pager->add_condition('document_incoming_invoice.paid', 0);
		$pager->page(true);

		$template->assign('unpaid_invoices', $pager->items);
	}

	public function display_download() {
		if (!isset($_POST['invoice'])) {
			Session::redirect('/administrative/document/invoice/pay');
		}

		$document_ids = [];
		foreach ($_POST['invoice'] as $document_id => $dummy) {
			$document_ids[] = $document_id;
		}

		if (!in_array($_POST['export_format'], [ 'Export_Payment_Belfius', 'Export_Payment_Sepa' ])) {
			Session::redirect('/administrative/document/invoice/pay');
		}

		$data = [
			'document_ids' => $document_ids,
		];
		if (isset($_POST['mark_paid'])) {
			$data['mark_paid'] = true;
		} else {
			$data['mark_paid'] = false;
		}

		$export = new $_POST['export_format']();
		$export->data = json_encode($data);
		$export->save();
		$export->run();

		Session::redirect('/export?action=created');
	}
	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.document';
	}

}
