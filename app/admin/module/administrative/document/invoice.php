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

		if (isset($_POST['supplier_id'])) {
			if ($_POST['supplier_id'] > 0) {
				$pager->add_condition('document_incoming_invoice.supplier_id', $_POST['supplier_id']);
			}
		}

		// Fix for pager
		$pager->add_condition('document_incoming_invoice.document_id', '>', 0);

		$pager->add_condition('classname', 'Document_Incoming_Invoice');
		$pager->add_join('document_incoming_invoice', 'document_id', 'document.id');

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('date');
		$pager->add_sort_permission('title');
		$pager->add_sort_permission('paid');
		$pager->add_sort_permission('document_incoming_invoice.expiration_date');
		$pager->add_sort_permission('supplier.company');
		$pager->add_sort_permission('document_incoming_invoice.price_incl');

		$pager->set_sort('date');
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
		$template->assign('suppliers', Supplier::get_all('company'));
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

		if (isset($_POST['document'])) {
			if (isset($_POST['payment_message_type'])) {
				if ($_POST['payment_message_type'] == 'payment_message_type_structured') {
					$_POST['document']['payment_message'] = '';
				} else {
					$_POST['document']['payment_structured_message'] = '';
				}
			}

			if (isset($_POST['document']['paid'])) {
				$_POST['document']['paid'] = true;
			} else {
				$_POST['document']['paid'] = false;
			}

		}


		parent::display_edit();
	}


}
