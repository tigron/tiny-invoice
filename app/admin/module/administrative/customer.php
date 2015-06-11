<?php
/**
 * Web Module Administrative Customer
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_Administrative_Customer extends Web_Module {
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
		$template = Web_Template::Get();

		$pager = new Web_Pager('customer');
		$permissions = [
			'company' => 'company',
			'firstname' => 'firstname',
			'lastname' => 'lastname'
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

		if (isset($_POST['customer'])) {
			$customer = new Customer();
			$customer->load_array($_POST['customer']);
			if ($customer->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$customer->save();

				$session = Web_Session_Sticky::Get();
				$session->message = 'created';
				Web_Session::Redirect('/administrative/customer');
			}
		}

		$template->assign('languages', Language::get_all());
		$template->assign('countries', Country::get_grouped());
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Web_Template::Get();
		$customer = Customer::get_by_id($_GET['id']);

		if (isset($_POST['customer'])) {
			$customer->load_array($_POST['customer']);
			if ($customer->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$customer->save();

				$session = Web_Session_Sticky::Get();
				$session->message = 'updated';
				Web_Session::Redirect('/administrative/customer?action=edit&id=' . $customer->id);
			}
		}
		$template->assign('customer', $customer);
		$template->assign('languages', Language::get_all());
		$template->assign('countries', Country::get_grouped());
	}

	/**
	 * Search customer (Ajax)
	 *
	 * @access public
	 */
	public function display_ajax_search() {
		$this->template = null;

		$pager = new Web_Pager('customer');
		$permissions = [
			'lastname' => 'lastname'
		];
		$pager->set_sort_permissions($permissions);
		$pager->set_search($_GET['search']);
		$pager->page();

		$data = [];
		foreach ($pager->items as $customer) {
			$name = $customer->firstname . ' ' . $customer->lastname;
			if ($customer->company != '') {
				$name .= ' (' . $customer->company . ')';
			}
			$data[] = [
				'id' => $customer->id,
				'value' => $name
			];
		}
		echo json_encode($data);
	}

	/**
	 * Load customer (Ajax)
	 *
	 * @access public
	 */
	public function display_load_customer() {
		$this->template = null;

		$customer = Customer::get_by_id($_GET['id']);
		echo json_encode($customer->get_info());
	}

}
