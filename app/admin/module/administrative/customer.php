<?php
/**
 * Web Module Administrative Customer
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Customer extends Module {
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
	public $template = 'administrative/customer.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();

		$pager = new Pager('customer');

		$pager->add_sort_permission('company');
		$pager->add_sort_permission('firstname');
		$pager->add_sort_permission('lastname');

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

		if (isset($_POST['customer'])) {
			$customer = new Customer();
			$customer->load_array($_POST['customer']);
			if ($customer->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$customer->save();

				Session::set_sticky('message', 'created');
				Session::redirect('/administrative/customer');
			}
		}

		$template->assign('languages', Language::get_all());
		$template->assign('countries', Country::get_grouped());
	}

	/**
	 * Search customer (ajax)
	 *
	 * @access public
	 */
	public function display_ajax_search() {
		$this->template = null;

		$pager = new Pager('customer');
		$pager->add_sort_permission('lastname');
		$pager->set_search($_GET['search']);
		$pager->page();

		$data = [];
		foreach ($pager->items as $customer) {
			$data[] = [
				'id' => $customer->id,
				'value' => $customer->get_display_name(),
			];
		}
		echo json_encode($data);
	}

	/**
	 * Export cutomers
	 *
	 * @access public
	 */
	public function display_export() {
		$template = Template::Get();

		$pager = new Pager('customer');
		$pager->add_sort_permission('company');
		$pager->page();
		$template->assign('pager', $pager);

		if (isset($_POST['export_format'])) {
			$export = new $_POST['export_format']();
			$export->data = json_encode([]);
			$export->save();
			$export->run();

			Session::redirect('/export?action=created');
		}

	}

	/**
	 * Load customer (ajax)
	 *
	 * @access public
	 */
	public function display_load_customer() {
		$this->template = null;

		$customer = Customer::get_by_id($_GET['id']);
		echo json_encode($customer->get_info());
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.customer';
	}

}
