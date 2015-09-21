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
		$template = Template::Get();

		$pager = new Pager('purchase');

		$pager->add_sort_permission('supplier.company');
		$pager->add_sort_permission('expiration_date');
		$pager->add_sort_permission('paid');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		$template = Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Add
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Template::Get();

		if (isset($_POST['purchase'])) {
			$purchase = new Purchase();
			$purchase->load_array($_POST['purchase']);
			if ($purchase->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$purchase->save();

				$session = Session_Sticky::Get();
				$session->message = 'created';
				Session::Redirect('/administrative/purchase');
			}
		}
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
			$purchase->save();

			$session = Session_Sticky::Get();
			$session->message = 'updated';
			Session::Redirect('/administrative/purchase?action=edit&id=' . $purchase->id);
		}
		$template->assign('purchase', $purchase);
	}

}
