<?php
/**
 * Invoice_Queue class
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Invoice_Queue {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Validate data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = []) {
		$required_fields = array('description', 'qty', 'price');
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get price excl
	 *
	 * @access public
	 * @return decimal $price_excl
	 */
	public function get_price_excl() {
		return $this->price * $this->qty;
	}

	/**
	 * Is processed
	 *
	 * @access public
	 * @return bool $processed
	 */
	public function is_processed() {
		if (is_null($this->processed_to_invoice_item_id)) {
			return false;
		}

		return true;
	}

	/**
	 * Get by Invoice_Contact
	 *
	 * @access public
	 * @param Invoice_Contact $invoice_contact
	 * @return array Invoice_Queue $items
	 */
	public static function get_by_invoice_contact(Invoice_Contact $invoice_contact) {
		$table = self::trait_get_database_table();
		$db = self::trait_get_database();
		$ids = $db->getCol('SELECT id FROM ' . $table . ' WHERE invoice_contact_id = ?', [ $invoice_contact->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get unprocessed by Invoice_Contact
	 *
	 * @access public
	 * @param Invoice_Contact $invoice_contact
	 * @return array Invoice_Queue $items
	 */
	public static function get_unprocessed_by_invoice_contact(Invoice_Contact $invoice_contact) {
		$table = self::trait_get_database_table();
		$db = self::trait_get_database();
		$ids = $db->getCol('SELECT id FROM ' . $table . ' WHERE invoice_contact_id = ? AND processed_to_invoice_item_id IS NULL', [ $invoice_contact->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}
}
