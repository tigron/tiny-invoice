<?php
/**
 * Web Module Administrative Customer
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Administrative\Customer;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Application\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Document extends Module {
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
	public $template = 'administrative/customer/document.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();
		$customer = \Customer::get_by_id($_GET['id']);
		$template->assign('customer', $customer);

		$contracts = \Document_Contract::get_by_customer($customer);
		$documentations = \Document_Documentation::get_by_customer($customer);

		$sorted = [];
		foreach ($contracts as $contract) {
			$sorted[$contract->id] = $contract;
		}

		foreach ($documentations as $documentation) {
			$sorted[$documentation->id] = $documentation;
		}
		$template->assign('documents', $sorted);
	}
}
