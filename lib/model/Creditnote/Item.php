<?php
/**
 * Invoice_Item class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Creditnote_Item {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
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
			$this->vat_rate_value = 0;
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
	public function validate(&$errors = []) {
		$required_fields = ['description', 'qty', 'price_incl', 'price_excl'];

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
		return $this->price_excl * $this->qty;
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
	 * Get by creditnote
	 *
	 * @access public
	 * @param Creditnote $creditnote
	 * @return array $creditnote_items
	 */
	public static function get_by_creditnote(Creditnote $creditnote) {
		$table = self::trait_get_database_table();
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE creditnote_id = ?', [ $creditnote->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}
}
