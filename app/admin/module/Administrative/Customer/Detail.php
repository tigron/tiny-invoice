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

class Detail extends Module {
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
	public $template = 'administrative/customer/detail.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();
		$customer = \Customer::get_by_id($_GET['id']);

		$template->assign('customer', $customer);
		$template->assign('languages', \Language::get_all());
		$template->assign('countries', \Country::get_grouped());
	}

	/**
	 * Edit customer
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::get();
		$customer = \Customer::get_by_id($_GET['id']);
		$customer->load_array($_POST['customer']);
		$customer->validate($errors);

		if (isset($_POST['ignore_vat'])) {
			unset($errors['vat']);
		}

		if (count($errors) > 0) {
			$template->assign('errors', $errors);
			$this->display();
			return;
		} else {
			$customer->save(false);

			Session::set_sticky('message', 'updated');
			Session::redirect('/administrative/customer/detail?id=' . $customer->id);
		}
	}
}
