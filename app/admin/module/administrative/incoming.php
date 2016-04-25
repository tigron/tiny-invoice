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
	 * merge
	 *
	 * @access public
	 */
	public function display_merge() {
		$incoming = Incoming::get_by_id($_GET['id']);

		$incoming_pages = [];
		if (isset($_POST['selected_pages']) AND !empty($_POST['selected_pages'])) {
			$pages = explode(',', $_POST['selected_pages']);
			foreach ($pages as $page) {
				$id = str_replace('page_', '', $page);
				$incoming_page = Incoming_Page::get_by_id($id);
				$incoming_pages[] = $incoming_page;
			}
		}

		$pdf_pages = [];
		foreach ($incoming_pages as $incoming_page) {
			$pdf_pages[] = $incoming_page->file;
		}

		if (count($incoming_pages) > 0) {
			$pdf = \Skeleton\File\Pdf\Pdf::merge($incoming_pages[0]->incoming->file->name, $pdf_pages);
		} else {
			$pdf = $incoming->file->copy();
		}

		$document = new Document();
		$document->title = $incoming->subject;
		$document->file_id = $pdf->id;
		$document->classname = 'Document';
		$document->save();

		foreach ($incoming_pages as $incoming_page) {
			$incoming_page->delete();
		}

		if (count($incoming->get_incoming_pages()) == 0) {
			$incoming->delete();
		}
		Session::redirect('/administrative/document?action=edit&id=' . $document->id);
	}

	/**
	 * Remove page (ajax)
	 *
	 * @access public
	 */
	public function display_remove_page() {
		$this->template = false;

		$page = Incoming_Page::get_by_id($_GET['id']);
		$page->delete();
	}

	/**
	 * Delete incoming document
	 *
	 * @access public
	 */
	public function display_delete() {
		$incoming = Incoming::get_by_id($_GET['id']);
		$incoming->delete();

		Session::set_sticky('message', 'deleted');
		Session::redirect('/administrative/incoming');
	}
}
