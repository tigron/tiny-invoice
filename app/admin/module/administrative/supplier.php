<?php
/**
 * Web Module Administrative Supplier
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Supplier extends Module {
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
		$template = Template::Get();

		$pager = new Pager('supplier');

		$pager->add_sort_permission('company');
		$pager->add_sort_permission('vat');
		$pager->add_sort_permission('country.name');
		$pager->add_sort_permission('city');

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

		if (isset($_POST['supplier'])) {
			$supplier = new Supplier();
			$supplier->load_array($_POST['supplier']);
			if ($supplier->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$supplier->save();

				Session::set_sticky('message', 'created');
				Session::Redirect('/administrative/supplier');
			}
		}
		$template->assign('countries', Country::get_grouped());
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::Get();
		$supplier = Supplier::get_by_id($_GET['id']);

		if (isset($_POST['supplier'])) {
			$supplier->load_array($_POST['supplier']);
			$supplier->save();

			Session::set_sticky('message', 'updated');
			Session::Redirect('/administrative/supplier?action=edit&id=' . $supplier->id);
		}
		$template->assign('supplier', $supplier);
		$template->assign('countries', Country::get_grouped());
	}

}
