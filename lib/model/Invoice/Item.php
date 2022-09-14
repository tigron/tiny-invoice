<?php
/**
 * Invoice_Item class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Invoice_Item {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Delete;
	use \Skeleton\Object\Save {
		save as trait_save;
	}

	/**
	 * Save
	 *
	 * @access public
	 * @param boolean $validate
	 */
	public function save($validate = true) {
		$this->calculate_prices(false);
		$this->trait_save($validate);
	}

	/**
	 * Calculate prices
	 *
	 * @access private
	 */
	public function calculate_prices($save = true) {
		if (isset($this->price_incl) || isset($this->price_excl)) {
			$empty = false;
		} else {
			$empty = true;
		}

		if (!isset($this->price_incl)) {
			$this->price_incl = 0;
		}

		if (!isset($this->price_excl)) {
			$this->price_excl = 0;
		}

		if ($this->price_incl != 0 && $this->price_excl != 0 || (empty($this->price_incl) && empty($this->price_excl) && $empty)) {
			return;
		}

		if (empty($this->vat_rate_value)) {
			return;
		}

		if ($this->price_incl != 0) {
			$this->price_excl = $this->price_incl / (1 + ($this->vat_rate_value / 100));
		}

		if ($this->price_excl != 0) {
			$this->price_incl = $this->price_excl + ($this->price_excl * ($this->vat_rate_value / 100));
		}

		if ($save === true) {
			$this->save();
		}
	}

	/**
	 * Validate data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = null) {
		$errors = [];
		$required_fields = ['description', 'qty', 'price_incl', 'price_excl'];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] === '') {
				$errors[$required_field] = 'required';
			}
		}

		// Check if we have numeric values
		$numeric_values = ['qty', 'price_incl', 'price_excl'];
		foreach ($numeric_values as $numeric_value) {
			if (!is_numeric($this->details[$numeric_value])) {
				$errors[$numeric_value] = 'non-numeric';
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
		return round($this->price_excl * $this->qty, 2);
	}

	/**
	 * Get price incl
	 *
	 * @access public
	 * @return decimal $price_incl
	 */
	public function get_price_incl() {
		return $this->price_incl * $this->qty;
	}

	/**
	 * Get by invoice
	 *
	 * @access public
	 * @param Invoice $invoice
	 * @return array Invoice_Item $items
	 */
	public static function get_by_invoice(Invoice $invoice) {
		$table = self::trait_get_database_table();
		$db = Database::get();
		$data = $db->get_all('SELECT * FROM ' . $table . ' WHERE invoice_id = ?', [ $invoice->id ]);

		$items = [];
		foreach ($data as $row) {
			$item = new self();
			$item->id = $row['id'];
			$item->details = $row;
			$items[] = $item;
		}

		return $items;
	}
}
