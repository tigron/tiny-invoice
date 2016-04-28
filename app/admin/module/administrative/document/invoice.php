<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

require_once dirname(__FILE__) . '/../document.php';

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Document_Invoice extends Web_Module_Administrative_Document {

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
		$pager = new Pager('document');

		$selected_tags = [];
		$tag_ids = [];
		if (!empty($_POST['tag_ids'])) {
			$tag_ids = explode(',', $_POST['tag_ids']);
			if (count($tag_ids) > 0) {
				foreach ($tag_ids as $tag_id) {
					$tag = Tag::get_by_id($tag_id);
					$selected_tags[] = $tag;
				}
			}
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
			$pager->add_condition('document_incoming_invoice.paid', 0);
		}

		$pager->add_condition('classname', 'Document_Incoming_Invoice');
		$pager->add_join('document_incoming_invoice', 'document_id', 'document.id');

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('created');
		$pager->add_sort_permission('title');
		$pager->add_sort_permission('paid');
		$pager->add_sort_permission('document_incoming_invoice.price_incl');

		$pager->set_sort('created');
		$pager->set_direction('DESC');
		if (isset($_GET['search'])) {
			$pager->set_condition('%search%', $_GET['search']);
		}
		if (isset($_GET['tags'])) {
			$pager->set_condition('tag_id', $_GET['tags']);
		}
		$pager->page();

		$template = Template::get();
		$template->assign('pager', $pager);
		$template->assign('tags', Tag::get_all());
		$template->assign('selected_tags', $selected_tags);
	}

	/**
	 * Edit an incoming invoice
	 *
	 * @access public
	 */
	public function display_edit() {
		$document = Document::get_by_id($_GET['id']);
		if ($document->classname != 'Document_Incoming_Invoice') {
			Session::redirect('/administrative/document?action=edit&id=' . $_GET['id']);
		}
		parent::display_edit();
	}


}
