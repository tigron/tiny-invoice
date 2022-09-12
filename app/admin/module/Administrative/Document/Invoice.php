<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */


namespace App\Admin\Module\Administrative\Document;

use \Skeleton\Core\Web\Template;
use \Skeleton\Database\Database;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Invoice extends \App\Admin\Module\Administrative\Document {

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = 'administrative/document/invoice.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();
		$db = Database::get();
		$pager = new Pager('document');

		if (isset($_POST['advanced']) and $_POST['advanced'] == 1) {
			Session::set_sticky('advanced', true);
		} else {
			unset($_POST['tag_ids']);
			unset($_POST['supplier_id']);
			unset($_POST['paid']);
			unset($_POST['accounting_identifier']);
		}


		$selected_tags = [];
		$tag_ids = [];
		if (!empty($_POST['tag_ids'])) {
			$tag_ids = explode(',', $_POST['tag_ids']);
			if (count($tag_ids) > 0) {
				foreach ($tag_ids as $tag_id) {
					$tag = \Tag::get_by_id($tag_id);
					$selected_tags[] = $tag;
				}
			}
		}

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}

		if (count($tag_ids) > 0) {
			$pager->add_join('document_tag', 'document_id', 'document.id');
			$pager->add_condition('document_tag.tag_id', 'IN', $tag_ids);
		}

		if (isset($_POST['paid'])) {
			if ($_POST['paid'] == 1) {
				$pager->add_condition('document_incoming_invoice.paid', 1);
			} elseif ($_POST['paid'] == 0) {
				$pager->add_condition('document_incoming_invoice.paid', 0);
			} else {
				$pager->clear_condition('document_incoming_invoice.paid');
			}
		} else {
			$pager->clear_condition('document_incoming_invoice.paid');
		}

		if (isset($_POST['accounting_identifier'])) {
			if ($_POST['accounting_identifier'] == 'empty') {
				$sql = "SELECT document_id
						FROM  document_incoming_invoice
						WHERE 1
						AND accounting_identifier = ''
						OR accounting_identifier IS NULL";
				$ids = $db->get_column($sql);
				$pager->add_condition('document.id', 'IN', $ids);
			} elseif ($_POST['accounting_identifier'] == 'not_empty') {
				$pager->add_condition('document_incoming_invoice.accounting_identifier', '!=', '');
				$pager->add_condition('document_incoming_invoice.accounting_identifier', "IS NOT", NULL);
			} else {
				$pager->clear_condition('document_incoming_invoice.accounting_identifier');
			}
		} else {
			$pager->clear_condition('document_incoming_invoice.accounting_identifier');
		}

		if (isset($_POST['supplier_id'])) {
			if ($_POST['supplier_id'] > 0) {
				$pager->add_condition('document_incoming_invoice.supplier_id', $_POST['supplier_id']);
			}
		}

		// Fix for pager
		$pager->add_condition('document_incoming_invoice.document_id', '>', 0);
		$pager->add_condition('classname', 'Document_Incoming_Invoice');
		$pager->add_join('document_incoming_invoice', 'document_id', 'document.id');
		$pager->add_join('supplier', 'id', 'document_incoming_invoice.supplier_id');

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('document.date');
		$pager->add_sort_permission('title');
		$pager->add_sort_permission('document_incoming_invoice.paid');
		$pager->add_sort_permission('document_incoming_invoice.accounting_identifier');
		$pager->add_sort_permission('document_incoming_invoice.expiration_date');
		$pager->add_sort_permission('supplier.company');
		$pager->add_sort_permission('document_incoming_invoice.price_incl');
		$pager->add_sort_permission('document_incoming_invoice.price_excl');

		$pager->set_sort('document.date');
		$pager->set_direction('DESC');
		$pager->page();

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/administrative/document/invoice');
		}


		$conditions = $pager->get_conditions();
		if (isset($conditions['document_tag.tag_id'])) {
			$tag_ids = $conditions['document_tag.tag_id'][0]->get_value();
			$selected_tags = [];
			foreach ($tag_ids as $tag_id) {
				$selected_tags[] = \Tag::get_by_id($tag_id);
			}
			$template->assign('selected_tags', $selected_tags);
		}
		if (isset($conditions['document_incoming_invoice.supplier_id'])) {
			$values = $conditions['document_incoming_invoice.supplier_id'][0]->get_value();
			$supplier_id = array_shift($values);
			$supplier = \Supplier::get_by_id($supplier_id);
			$template->assign('selected_supplier', $supplier);
		}

		$template->assign('pager', $pager);
		$template->assign('tags', \Tag::get_all());
	}

	/**
	 * Edit an incoming invoice
	 *
	 * @access public
	 */
	public function display_edit() {
		$document = \Document::get_by_id($_GET['id']);
		if ($document->classname != 'Document_Incoming_Invoice') {
			Session::redirect('/administrative/document?action=edit&id=' . $_GET['id']);
		}

		parent::display_edit();
	}

	/**
	 * AJAX: check supplier_identifier
	 *
	 * @access public
	 */
	public function display_check_supplier_identifier() {
		$this->template = null;

		try {
			$supplier = \Supplier::get_by_id($_POST['supplier_id']);
			$invoices = \Document_Incoming_Invoice::get_by_supplier_supplier_identifier($supplier, $_POST['supplier_identifier']);
			foreach ($invoices as $key => $invoice) {
				if ($invoice->id == $_POST['document_id']) {
					unset($invoices[$key]);
				}
			}
			echo count($invoices);
		} catch (\Exception $e) {
			echo 0;
		}
	}

	/**
	 * Export cutomers
	 *
	 * @access public
	 */
	public function display_export() {
		$export = new \Export_Excel_Document_Invoice();
		$export->data = json_encode($_REQUEST['hash']);
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
