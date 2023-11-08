<?php
/**
 * Web Module Administrative Customer
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Administrative\Customer;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;
use \Skeleton\Pager\Web\Pager;

class Invoice extends Module {
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
		$customer = \Customer::get_by_id($_GET['id']);
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

		$expired_invoices = \Invoice::get_expired_by_customer($customer);
		$template->assign('expired_invoices', $expired_invoices);
	}
}
