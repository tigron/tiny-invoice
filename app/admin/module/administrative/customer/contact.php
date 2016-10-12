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

		if ($updated AND isset($_POST['customer_contact_id']) AND $_POST['customer_contact_id'] != 0) {
			$customer_contact->active = false;
			$customer_contact->save(false);
		}

		$new_customer_contact = new Customer_Contact();
		$new_customer_contact->load_array($_POST['customer_contact']);
		$new_customer_contact->customer_id = $customer->id;
		$new_customer_contact->active = true;
		$new_customer_contact->validate($errors);

		if ($new_customer_contact->validate($errors)) {
			$new_customer_contact->save();

			$invoice_queue_recurring_groups = Invoice_Queue_Recurring_Group::get_by_customer_contact($customer_contact);
			foreach ($invoice_queue_recurring_groups as $invoice_queue_recurring_group) {
				$invoice_queue_recurring_group->customer_contact_id = $new_customer_contact->id;
				$invoice_queue_recurring_group->save();
			}

			$invoice_queue = Invoice_Queue::get_unprocessed_by_customer_contact($customer_contact);
			foreach ($invoice_queue as $invoice_queue_item) {
				$invoice_queue_item->customer_contact_id = $new_customer_contact->id;
				$invoice_queue_item->save();
			}

			Session::set_sticky('message', 'customer_contact_updated');
			Session::set_sticky('updated_customer_contact_id', $new_customer_contact->id);
			Session::redirect('/administrative/customer/contact?id=' . $customer->id);
		} else {
			$template = Template::Get();
			$template->assign('customer_contact_errors', $errors);
			print_r($errors);
			print_r($new_customer_contact);
			$template->assign('action', 'edit');

			$this->display();
		}
	}
}
