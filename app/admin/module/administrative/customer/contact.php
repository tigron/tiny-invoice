<?php
/**
 * Customer module
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Customer_Contact extends Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'administrative/customer/contact.twig';

	/**
	 * Display
	 * This is the default method for a module.
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();
		$customer = Customer::get_by_id($_GET['id']);
		$template->assign('customer', $customer);
		$template->assign('countries', Country::get_grouped('all'));
	}

	/**
	 * Edit contacts
	 *
	 * @access public
	 */
	public function display_edit() {
		$customer = Customer::get_by_id($_GET['id']);

		$updated = false;
		if (isset($_POST['customer_contact_id']) AND $_POST['customer_contact_id'] != 0) {

			$customer_contact = Customer_Contact::get_by_id($_POST['customer_contact_id']);

			if (isset($_POST['delete_contact'])) {
				$customer_contact->archive();
				Session::set_sticky('message', 'contact_removed');
				Session::redirect('/administrative/customer/contact?id=' . $customer->id);
			}

			foreach ($_POST['customer_contact'] as $key => $value) {
				if ($customer_contact->$key != $value) {
					$updated = true;
				}
			}

		} else {
			$updated = true;
		}

		if (!$updated) {
			Session::set_sticky('message', 'contact_no_update');
			Session::set_sticky('updated_customer_contact_id', $customer_contact->id);
			Session::redirect('/administrative/customer/contact?id=' . $customer->id);
		}

		if ($updated AND isset($_POST['customer_contact_id']) AND $_POST['customer_contact_id'] != 0 AND $customer_contact->validate()) {
			$customer_contact->active = false;
			$customer_contact->save(false);
		}

		$customer_contact = new Customer_Contact();
		$customer_contact->load_array($_POST['customer_contact']);
		$customer_contact->customer_id = $customer->id;
		if ($customer_contact->validate($errors)) {
			$customer_contact->save();

			Session::set_sticky('message', 'contact_updated');
			Session::set_sticky('updated_customer_contact_id', $customer_contact->id);
			Session::redirect('/administrative/customer/contact?id=' . $customer->id);
		} else {
			$template = Template::Get();
			$template->assign('customer_contact_errors', $errors);
			$template->assign('action', 'edit');

			$this->display();

		}
	}
}
