<?php
/**
 * Web Module Administrative Purchase
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Purchase extends Module {
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
	protected $template = 'administrative/purchase.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$pager = new Pager('purchase');

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('supplier.company');
		$pager->add_sort_permission('expiration_date');
		$pager->add_sort_permission('paid');
		$pager->add_sort_permission('price_incl');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		$template = Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Add purchase
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Template::get();
		$purchase = null;

		if (isset($_GET['document_id'])) {
			$purchase = new Purchase();
			$document = Document::get_by_id($_GET['document_id']);
			$purchase->document_id = $document->id;
		}

		if (isset($_POST['purchase'])) {
			$purchase = new Purchase();
			$purchase->load_array($_POST['purchase']);
			if ($purchase->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$purchase->save();

				Session::set_sticky('message', 'created');
				Session::redirect('/administrative/purchase');
			}
		}

		$template->assign('purchase', $purchase);
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::Get();
		$purchase = Purchase::get_by_id($_GET['id']);

		if (isset($_POST['purchase'])) {
			$purchase->load_array($_POST['purchase']);
			if ($purchase->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$purchase->save();

				Session::set_sticky('message', 'updated');
				Session::redirect('/administrative/purchase?action=edit&id=' . $purchase->id);
			}
		}

		$template->assign('purchase', $purchase);
	}

	/**
	 * Display delete
	 *
	 * @access public
	 */
	public function display_delete() {
		$purchase = Purchase::get_by_id($_GET['id']);
		$purchase->delete();

		Session::set_sticky('message', 'deleted');
		Session::redirect('/administrative/purchase');
	}

}
