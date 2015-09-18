<?php
/**
 * Web Module Administrative Purchase
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_Administrative_Purchase extends Web_Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = true;

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = 'administrative/purchase.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Web_Template::Get();

		$pager = new Web_Pager('purchase');

		$pager->add_sort_permission('supplier.company');
		$pager->add_sort_permission('expiration_date');
		$pager->add_sort_permission('paid');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		$template = Web_Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Add
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Web_Template::Get();

		if (isset($_POST['purchase'])) {
			$purchase = new Purchase();
			$purchase->load_array($_POST['purchase']);
			if ($purchase->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$purchase->save();

				$session = Web_Session_Sticky::Get();
				$session->message = 'created';
				Web_Session::Redirect('/administrative/purchase');
			}
		}
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Web_Template::Get();
		$purchase = Purchase::get_by_id($_GET['id']);

		if (isset($_POST['purchase'])) {
			$purchase->load_array($_POST['purchase']);
			$purchase->save();

			$session = Web_Session_Sticky::Get();
			$session->message = 'updated';
			Web_Session::Redirect('/administrative/purchase?action=edit&id=' . $purchase->id);
		}
		$template->assign('purchase', $purchase);
	}

}
