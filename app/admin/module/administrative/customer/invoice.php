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

class Web_Module_Administrative_Customer_Invoice extends Module {
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
	public $template = 'administrative/customer/invoice.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();
		$customer = Customer::get_by_id($_GET['id']);
		$template->assign('customer', $customer);

		$pager = new Pager('invoice');
		$pager->add_sort_permission('id');
		$pager->add_sort_permission('number');
		$pager->add_sort_permission('created');
		$pager->add_sort_permission('expiration_date');
		$pager->add_condition('invoice.customer_id', $customer->id);
		$pager->set_sort('id');
		$pager->set_direction('DESC');
		$pager->page();

		$template->assign('pager', $pager);

		$expired_invoices = Invoice::get_expired_by_customer($customer);
		$template->assign('expired_invoices', $expired_invoices);
	}
}
