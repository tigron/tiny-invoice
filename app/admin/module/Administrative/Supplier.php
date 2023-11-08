<?php
/**
 * Web Module Administrative Supplier
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Administrative;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;
use \Skeleton\Pager\Web\Pager;

class Supplier extends Module {
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
	protected $template = 'administrative/supplier.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();

		$pager = new Pager('supplier');
		$pager->add_sort_permission('company');
		$pager->add_sort_permission('vat');
		$pager->add_sort_permission('accounting_identifier');
		$pager->add_sort_permission('city');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/administrative/supplier');
		}

		$template = Template::get();
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
			$_POST['supplier']['iban'] = str_replace(' ', '', $_POST['supplier']['iban']);
			$supplier = new \Supplier();

			$supplier->load_array($_POST['supplier']);
			if ($supplier->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$supplier->save();

				Session::set_sticky('message', 'created');
				Session::redirect('/administrative/supplier');
			}
		}

		$template->assign('countries', \Country::get_grouped());
	}

	/**
	 * Edit supplier (ajax)
	 *
	 * @access public
	 */
	public function display_ajax_edit() {
		$this->template = NULL;

		if (isset($_POST['supplier'])) {
			$supplier = \Supplier::get_by_id($_GET['id']);
			$supplier->load_array($_POST['supplier']);

			$errors = [];
			if ($supplier->validate($errors) === false) {
				echo json_encode($errors);
				return;
			}

			$supplier->save();
			echo json_encode($supplier->get_info());
		}
	}

	/**
	 * Search supplier (ajax)
	 *
	 * @access public
	 */
	public function display_ajax_search() {
		$this->template = null;

		$pager = new Pager('supplier');
		$pager->add_sort_permission('company');
		if (isset($_GET['q'])) {
			$pager->set_search($_GET['q']);
		}
		if (isset($_GET['page'])) {
			$_GET['p'] = $_GET['page'];
		}	
		$pager->set_sort('company');
		$pager->page();

		$data = [];
		foreach ($pager->items as $supplier) {
			$data[] = [
				'id' => $supplier->id,
				'text' => $supplier->company,
			];
		}

		$page_count = ceil($pager->item_count / \Skeleton\Pager\Config::$items_per_page);

		$result = [
			'results' => $data,
			'pagination' => [
				'more' => false,
			]
		];
		if ($pager->get_page() < $page_count) {
			$result['pagination']['more'] = true;
		}
		echo json_encode($result);
	}

	/**
	 * Load the suppliers
	 *
	 * @access public
	 */
	public function display_load_suppliers() {
		session_write_close();

		$this->template = NULL;

		$suppliers = \Supplier::get_all('company');
		$result = [];
		foreach ($suppliers as $supplier) {
			$result[] = $supplier->get_info();
		}
		echo json_encode($result);
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.supplier';
	}

}
