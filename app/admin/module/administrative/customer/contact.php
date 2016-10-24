<?php
/**
 * Web Module Administrative Invoice Contact
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;

class Web_Module_Administrative_Customer_Contact extends Module {
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
	public $template = null;

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$customer = Customer::get_by_id($_GET['id']);
		$template = Template::get();
		$template->assign('customer', $customer);
		$this->template = 'administrative/customer/contact.twig';

	}

	/**
	 * Add/update invoice contact
	 *
	 * @access public
	 */
	public function display_manage() {
		if (isset($_GET['id']) AND $_GET['id'] > 0) {
			$customer_contact = Customer_Contact::get_by_id($_GET['id']);
		} else {
			$customer_contact = new Customer_Contact();
		}

		$customer_contact->load_array($_POST['customer_contact']);
		if ($customer_contact->validate($errors) === false) {
			echo json_encode($errors);
		} else {
			$dirty_fields = $customer_contact->get_dirty_fields();
			unset($dirty_fields['invoice_method_id']);
			unset($dirty_fields['email']);


			if ($customer_contact->id !== null AND count($dirty_fields) > 0) {
				$customer_contact->active = false;
				$customer_contact->save();

				$new_customer_contact = new Customer_Contact();
				$new_customer_contact->load_array($_POST['customer_contact']);
				$new_customer_contact->active = true;
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

				echo json_encode($new_customer_contact->get_info());
			} else {
				$customer_contact->load_array($_POST['customer_contact']);
				$customer_contact->active = true;
				$customer_contact->save();

				echo json_encode($customer_contact->get_info());
			}

		}
	}

	/**
	 * Load customer_contact (Ajax)
	 *
	 * @access public
	 */
	public function display_load_customer_contact() {
		$this->template = null;

		$customer_contact = Customer_Contact::get_by_id($_GET['id']);
		echo json_encode($customer_contact->get_info());
	}

	public function display_load_countries() {
		$countries = Country::get_grouped();
		$result = [];
		foreach ($countries as $group => $country_list) {
			foreach ($country_list as $country) {
				if (!isset($result[$group])) {
					$result[$group] = [];
				}
				$result[$group][] = $country->get_info();
			}
		}
		echo json_encode($result);
	}

	/**
	 * Delete customer_contact (Ajax)
	 *
	 * @access public
	 */
	public function display_delete() {
		$this->template = null;

		$customer_contact = Customer_Contact::get_by_id($_GET['id']);
		$customer_contact->active = false;
		$customer_contact->save();

		echo json_encode([ 'status' => 1]);
	}
}
