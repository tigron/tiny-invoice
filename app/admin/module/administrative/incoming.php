<?php
/**
 * Web Module Administrative Invoice
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Incoming extends Module {
	/**
	 * Login required
	 *
	 * @access protected
	 * @var bool $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'administrative/incoming.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$pager = new Pager('incoming');

		$pager->set_direction('desc');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		$template = Template::get();
		$template->assign('pager', $pager);
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::get();
		$incoming = Incoming::get_by_id($_GET['id']);
		$template->assign('incoming', $incoming);
	}

	/**
	 * Add a document
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Template::get();

		$incoming_pages = [];
		if (isset($_POST['incoming_page_ids'])) {
			foreach ($_POST['incoming_page_ids'] as $incoming_page_id) {
				$incoming_pages[] = Incoming_Page::get_by_id($incoming_page_id);
			}
		}

		if (isset($_POST['purchase']['supplier_id'])) {
			try {
				$supplier = Supplier::get_by_id($_POST['purchase']['supplier_id']);
				$template->assign('supplier', $supplier);
			} catch (Exception $e) {
				unset($_POST['purchase']['supplier_id']);
			}
		}

		if (isset($_POST['document'])) {

			$selected_tags = [];
			if (!empty($_POST['tag_ids'])) {
				$tag_ids = explode(',', $_POST['tag_ids']);
				foreach ($tag_ids as $tag_id) {
					$selected_tags[] = Tag::get_by_id($tag_id);
				}
			}
			$document = new Document();
			$document->load_array($_POST['document']);
			$document->validate($document_errors);
			unset($document_errors['file_id']);

			$purchase_errors = [];
			if (isset($_POST['create_purchase'])) {
				$purchase = new Purchase();
				$purchase->load_array($_POST['purchase']);
				$purchase->validate($purchase_errors);
			}

			$errors = array_merge($document_errors, $purchase_errors);

			if (count($errors) > 0) {
				$template->assign('errors', $errors);
			} else {
				$pdf_pages = [];
				foreach ($incoming_pages as $incoming_page) {
					$pdf_pages[] = $incoming_page->file;
				}

				$pdf = \Skeleton\File\Pdf\Pdf::merge($incoming_pages[0]->incoming->file->name, $pdf_pages);
				$document->file_id = $pdf->id;
				$document->save();
				foreach ($selected_tags as $tag) {
					$document->add_tag($tag);
				}
				if (isset($_POST['create_purchase'])) {
					$purchase->save();
				}

				foreach ($incoming_pages as $incoming_page) {
					$incoming_page->delete();
					$incoming = $incoming_page->incoming;
				}
				if (count($incoming->get_incoming_pages()) == 0) {
					$incoming->delete();
				}
				Session::redirect('/administrative/document?action=edit&id=' . $document->id);
			}
		}

		$template->assign('incoming_pages', $incoming_pages);


	}

	/**
	 * merge
	 *
	 * @access public
	 */
	public function display_merge() {
		$pages = explode(',', $_POST['selected_pages']);
		$incoming_pages = [];
		$incoming = null;
		foreach ($pages as $page) {
			$id = str_replace('page_', '', $page);
			$incoming_page = Incoming_Page::get_by_id($id);
			$incoming_pages[] = $incoming_page;
			$incoming = $incoming_page->incoming;
		}
		$template = Template::get();
		$template->assign('incoming', $incoming);
		$template->assign('incoming_pages', $incoming_pages);
	}

	/**
	 * Remove page
	 *
	 * @access public
	 */
	public function display_remove_page() {
		$page = Incoming_Page::get_by_id($_GET['id']);
		$page->delete();
		$this->template = false;
	}
}
