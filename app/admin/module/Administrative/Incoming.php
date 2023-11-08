<?php
/**
 * Web Module Administrative Invoice
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Administrative;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;
use \Skeleton\Pager\Web\Pager;

class Incoming extends Module {
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

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/administrative/incoming');
		}

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
		try {
			$incoming = \Incoming::get_by_id($_GET['id']);
		} catch (\Exception $e) {
			Session::redirect('/administrative/incoming');
		}
		$template->assign('incoming', $incoming);
	}

	/**
	 * Rotate a page
	 *
	 * @access public
	 */
	public function display_rotate_page() {
		$page = \Incoming_Page::get_by_id($_GET['id']);
		$page->rotate();
		Session::redirect('/administrative/incoming?action=edit&id=' . $page->incoming_id);
	}

	/**
	 * merge
	 *
	 * @access public
	 */
	public function display_merge() {
		$incoming = \Incoming::get_by_id($_GET['id']);

		$incoming_pages = [];
		if (isset($_POST['selected_pages']) AND !empty($_POST['selected_pages'])) {
			$pages = explode(',', $_POST['selected_pages']);
			foreach ($pages as $page) {
				$id = str_replace('page_', '', $page);
				$incoming_page = \Incoming_Page::get_by_id($id);
				$incoming_pages[] = $incoming_page;
			}
		}

		$pdf_pages = [];
		foreach ($incoming_pages as $incoming_page) {
			$pdf_pages[] = $incoming_page->file;
		}

		if (count($incoming_pages) > 1) {
			$pdf = \Skeleton\File\Pdf\Pdf::merge($incoming_pages[0]->incoming->file->name, $pdf_pages);
		} elseif (count($incoming_pages) == 1) {
			$pdf = $incoming_pages[0]->file->copy();
		} else {
			$pdf = $incoming->file->copy();
		}

		$document = new \Document();
		if ($incoming->subject == '') {
			$document->title = 'Untitled document';
		} else {
			$document->title = $incoming->subject;
		}
		$document->file_id = $pdf->id;
		$document->classname = 'Document';
		$document->save();
		$document->date = $document->created;
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

		$page = \Incoming_Page::get_by_id($_GET['id']);
		$incoming = $page->incoming;
		$page->delete();

		$pages = $incoming->get_incoming_pages();
		if (count($pages) > 0) {
			Session::redirect('/administrative/incoming?action=edit&id=' . $incoming->id);
		} else {
			Session::redirect('/administrative/incoming');
		}
	}

	/**
	 * Delete incoming document
	 *
	 * @access public
	 */
	public function display_delete() {
		$incoming = \Incoming::get_by_id($_GET['id']);
		$incoming->delete();

		Session::set_sticky('message', 'deleted');
		Session::redirect('/administrative/incoming');
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
