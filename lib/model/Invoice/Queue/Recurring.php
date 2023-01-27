<?php
/**
 * Invoice_Queue_Recurring class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Invoice_Queue_Recurring {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * Validates the given input before insertion
	 *
	 * @param array The array that will contain the errors encountered. Passed by reference.
	 * @return array The array containing the errors
	 * @access public
	 */
	public function validate(&$errors = null) {
		$errors = [];
		$required_fields = ['name', 'price'];

		$errors = [];
		foreach ($required_fields as $field) {
			if (!isset($this->details[$field]) || $this->details[$field] == '') {
				$errors[$field] = 'required';
			}
		}

		if (count($errors) > 0) {
			return $errors;
		}
	}

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		$invoice_queue = new Invoice_Queue();
		$invoice_queue->customer_contact_id = $this->invoice_queue_recurring_group->customer_contact_id;

		$customer_contact = $invoice_queue->customer_contact;

		$invoice_queue->customer_id = $this->invoice_queue_recurring_group->customer_id;
		$invoice_queue->product_type_id = $this->product_type_id;

		$description = $this->description;

		$template = new \Skeleton\Template\Template();
		$root_path = realpath(dirname(__FILE__) . '/../../../../');
		$template->set_template_directory($root_path . '/store/invoice_queue_recurring/');
		$template->assign('invoice_queue_recurring', $this);
		$template->assign('invoice_queue_recurring_group', $this->invoice_queue_recurring_group);
		$description = trim($template->render('template.twig'));

		$invoice_queue->description = $description;
		$invoice_queue->price = $this->price;
		$invoice_queue->qty = $this->qty;

		$vat = $customer_contact->get_vat(Vat_Rate::get_by_id(1));
		$invoice_queue->vat_rate_id = $this->vat_rate_id;
		$invoice_queue->vat_rate_value = $this->vat_rate_value;
		$invoice_queue->save();

		$history = new Invoice_Queue_Recurring_History();
		$history->invoice_queue_recurring_id = $this->id;
		$history->invoice_queue_id = $invoice_queue->id;
		$history->save();

		return $invoice_queue;
	}

	/**
	 * Get by Invoice_Queue_Recurring_Group
	 *
	 * @access public
	 * @param Invoice_Queue_Recurring_Group $group
	 * @return array $invoice_queue_recurring
	 */
	public static function get_by_invoice_queue_recurring_group(Invoice_Queue_Recurring_Group $group) {
		$db = Database::Get();
		$ids = $db->get_column('SELECT id FROM invoice_queue_recurring WHERE invoice_queue_recurring_group_id=? AND archived is null', [$group->id]);
		$items = [];
		foreach ($ids as $id) {
			$items[] = Invoice_Queue_Recurring::get_by_id($id);
		}
		return $items;
	}
}
