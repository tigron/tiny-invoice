<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/model/Customer.php';
require_once LIB_PATH . '/base/Web/Pager.php';

class Module_Administrative_Customer extends Web_Module {
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
		if (isset($_POST['customer'])) {
			$customer = new Customer();
			$customer->load_array($_POST['customer']);
			$customer->save();
			Web_Session::Redirect('/administrative/customer');
		}

		$extra_conditions = array();
		if (isset($_POST['search'])) {
			$extra_conditions['%search%'] = $_POST['search'];
			$pager = new Web_Pager('customer', 'id', 'ASC', 1, $extra_conditions);
		} else {
			$pager = new Web_Pager('customer');
		}

		$template = Web_Template::Get();
		$template->assign('pager', $pager);

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
			$customer->save();
			$template->assign('saved', true);
		}

		$template->assign('countries', Country::get_grouped());
		$template->assign('customer', $customer);
	}
}
