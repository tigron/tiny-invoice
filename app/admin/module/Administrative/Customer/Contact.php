<?php
/**
 * Web Module Administrative Invoice Contact
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Administrative\Customer;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;

class Contact extends Module {
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
		$customer = \Customer::get_by_id($_GET['id']);
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
			$customer_contact = \Customer_Contact::get_by_id($_GET['id']);
		} else {
			$customer_contact = new \Customer_Contact();
		}

		$errors = [];
		$customer_contact->load_array($_POST['customer_contact']);
		$customer_contact->customer_id = \Customer::get_by_id($_GET['customer_id'])->id;
		if ($customer_contact->validate($errors) === false) {
			echo json_encode($errors);
		} else {
			$dirty_fields = $customer_contact->get_dirty_fields();
			unset($dirty_fields['invoice_method_id']);
			unset($dirty_fields['email']);
			unset($dirty_fields['phone']);
			unset($dirty_fields['fax']);
			unset($dirty_fields['mobile']);
			unset($dirty_fields['language_id']);
			unset($dirty_fields['reference']);
			unset($dirty_fields['alias']);

			if ($customer_contact->id !== null AND count($dirty_fields) > 0) {
				$customer_contact->active = false;
				$customer_contact->save();

				$new_customer_contact = new \Customer_Contact();
				$new_customer_contact->load_array($_POST['customer_contact']);
				$new_customer_contact->active = true;
				$new_customer_contact->save();

				$invoice_queue_recurring_groups = \Invoice_Queue_Recurring_Group::get_by_customer_contact($customer_contact);
				foreach ($invoice_queue_recurring_groups as $invoice_queue_recurring_group) {
					$invoice_queue_recurring_group->customer_contact_id = $new_customer_contact->id;
					$invoice_queue_recurring_group->save();
				}

				$invoice_queue = \Invoice_Queue::get_unprocessed_by_customer_contact($customer_contact);
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
	 * Get the invoice expiration reminder in PDF
	 *
	 * @access public
	 */
	public function display_invoice_reminder() {
		$customer_contact = \Customer_Contact::get_by_id($_GET['id']);
		$customer_contact->get_invoice_reminder_pdf()->client_download();
	}

	/**
	 * Load customer_contact (Ajax)
	 *
	 * @access public
	 */
	public function display_load_customer_contact() {
		session_write_close();
		$this->template = null;

		$customer_contact = \Customer_Contact::get_by_id($_GET['id']);
		echo json_encode($customer_contact->get_info());
	}

	/**
	 * Load the countries (AJAX)
	 *
	 * @access public
	 */
	public function display_load_countries() {
		session_write_close();

		$this->template = null;
		$countries = \Country::get_grouped();
		$result = [];
		foreach ($countries as $group => $country_list) {
			$country_group = [];
			$name_value = "text_" . $_SESSION['language']->name_short . "_name";

			foreach ($country_list as $country) {
				$country_group[] = ['id' => $country->id, 'text' => $country->$name_value, 'iso2' => $country->iso2];
			}
			$result[] = ['text' => $group,
						'children' => $country_group];
		}

		echo json_encode(['results' => $result]);
	}

	/**
	 * Load the languages
	 *
	 * @access public
	 */
	public function display_load_languages() {
		session_write_close();

		$this->template = null;
		$languages = \Language::get_all();
		$result = [];
		foreach ($languages as $language) {
			$result[] = ['id' => $language->id,
						'text'=> $language->name];
		}
		echo json_encode(['results' => $result]);
	}

	/**
	 * Delete customer_contact (Ajax)
	 *
	 * @access public
	 */
	public function display_delete() {
		$this->template = null;

		$customer_contact = \Customer_Contact::get_by_id($_GET['id']);
		$customer_contact->active = false;
		$customer_contact->save();

		echo json_encode([ 'status' => 1]);
	}
}
