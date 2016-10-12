<?php
/**
 * Invoice_Queue_Recurring_Group class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @author Lionel Laffineur <lionel@tigron.be>
 */

use \Skeleton\Database\Database;

class Invoice_Queue_Recurring_Group {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Validates the given input before insertion
	 *
	 * @param array The array that will contain the errors encountered. Passed by reference.
	 * @return array The array containing the errors
	 * @access public
	 */
	public function validate(&$errors = []) {
		$config = Config::Get();

		$required_fields = array('name', 'repeat_every', 'next_run', 'customer_contact_id');

		$errors = array();
		foreach ($required_fields as $field) {
			if (!isset($this->details[$field]) || $this->details[$field] == '') {
				$errors[$field] = $field;
			}
		}
		if (count($errors) > 0) {
			return $errors;
		}
	}

	/**
	 * Get total price
	 *
	 * @access public
	 * @return double $price
	 */
	public function get_total_price() {
		$items = $this->get_invoice_queue_recurring();
		$total = 0;
		foreach ($items as $item) {
			$total += $item->price;
		}
		return $total;
	}

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		$next_run = strtotime($this->next_run);
		if ($next_run > time()) {
			throw new Exception('Not runnable');
		}

		foreach ($this->get_invoice_queue_recurring() as $item) {
			$item->run();
		}

		if ($this->next_run == '0000-00-00 00:00:00') {
			$next_run = time();
		} else {
			$next_run = strtotime($this->next_run);
		}
		$next_run = strtotime($this->repeat_every, $next_run);
		$this->next_run = date('Y-m-d H:i:s', $next_run);
		$this->save();
	}

	/**
	 * Get invoice_queue_recurring
	 *
	 * @access public
	 * @return array $invoice_queue_recurring
	 */
	public function get_invoice_queue_recurring() {
		return Invoice_Queue_Recurring::get_by_invoice_queue_recurring_group($this);
	}

	/**
	 * Get runnable
	 *
	 * @access public
	 * @return array $invoice_queue_recurring_groups
	 */
	public static function get_runnable() {
		$db = Database::Get();
		$ids = $db->get_column('SELECT id FROM invoice_queue_recurring_group WHERE archived = "0000-00-00 00:00:00" AND next_run < NOW() AND (next_run < stop_after OR stop_after = "0000-00-00 00:00:00")', array());
		$groups = array();
		foreach ($ids as $id) {
			$groups[] = Invoice_Queue_Recurring_Group::get_by_id($id);
		}
		return $groups;
	}

	/**
	 * Get by Customer Contact
	 *
	 * @access public
	 * @param Customer Contact $customer_contact
	 * @return array $invoice_queue_recurring_groups
	 */
	public static function get_by_customer_contact(Customer_Contact $customer_contact) {
		$db = Database::Get();
		$ids = $db->get_column('SELECT id FROM invoice_queue_recurring_group WHERE archived = "0000-00-00 00:00:00" AND customer_contact_id = ?', [$customer_contact->id]);
		$groups = array();
		foreach ($ids as $id) {
			$groups[] = Invoice_Queue_Recurring_Group::get_by_id($id);
		}
		return $groups;
	}
}
