<?php
/**
 * Web Module Administrative Supplier
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_Administrative_Supplier extends Web_Module {
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
	public $template = 'administrative/supplier.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Web_Template::Get();

		$pager = new Web_Pager('supplier');
		$permissions = [
			'company' => 'company',
			'vat' => 'vat'
		];
		$pager->set_sort_permissions($permissions);

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

		if (isset($_POST['supplier'])) {
			$supplier = new Supplier();
			$supplier->load_array($_POST['supplier']);
			if ($supplier->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$supplier->save();

				$session = Web_Session_Sticky::Get();
				$session->message = 'created';
				Web_Session::Redirect('/administrative/supplier');
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
		$supplier = Supplier::get_by_id($_GET['id']);

		if (isset($_POST['supplier'])) {
			$supplier->load_array($_POST['supplier']);
			$supplier->save();

			$session = Web_Session_Sticky::Get();
			$session->message = 'updated';
			Web_Session::Redirect('/administrative/supplier?action=edit&id=' . $supplier->id);
		}
		$template->assign('supplier', $supplier);
	}

}