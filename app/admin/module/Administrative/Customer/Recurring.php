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

class Recurring extends Module {
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
	public $template = 'administrative/customer/recurring.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();
		$customer = \Customer::get_by_id($_GET['id']);
		$template->assign('customer', $customer);

		$pager = new Pager('invoice_queue_recurring_group');
        $pager->add_condition('archived', '=', '0000-00-00 00:00:00');
		$pager->add_sort_permission('id');
		$pager->add_sort_permission('number');
		$pager->add_sort_permission('created');
		$pager->add_condition('customer_id', $customer->id);
		$pager->set_sort('id');
		$pager->set_direction('DESC');
		$pager->page();

		$template->assign('pager', $pager);
	}
}
