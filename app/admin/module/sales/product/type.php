<?php
/**
 * Web Module product_type
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Sales_Product_Type extends Module {
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
	protected $template = 'sales/product/type.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();

		$pager = new Pager('product_type');
		$pager->add_sort_permission('name');
		$pager->add_sort_permission('identifier');
		$pager->add_condition('archived', 'IS', NULL);

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/sales/product/type');
		}

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

		if (isset($_POST['product_type'])) {
			$product_type = new Product_Type();
			$product_type->load_array($_POST['product_type']);
			if ($product_type->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$product_type->save();

				Session::set_sticky('message', 'created');
				Session::redirect('/sales/product/type');
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
		$product_type = Product_Type::get_by_id($_GET['id']);

		if (isset($_POST['product_type'])) {
			$product_type->load_array($_POST['product_type']);
			if ($product_type->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$product_type->save();

				Session::set_sticky('message', 'updated');
				Session::redirect('/sales/product/type?action=edit&id=' . $product_type->id);
			}
		}

		$template->assign('product_type', $product_type);
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function display_delete() {
		$product_type = Product_Type::get_by_id($_GET['id']);
		$product_type->archive();
		Session::set_sticky('message', 'deleted');
		Session::redirect('/sales/product/type');
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.product_type';
	}
}
