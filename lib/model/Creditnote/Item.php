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
	use \Skeleton\Object\Save;

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
	 * Get price incl
	 *
	 * @access public
	 * @return decimal $price_incl
	 */
	public function get_price_incl() {
		$price_excl = $this->get_price_excl();
		return $price_excl + round(($price_excl / 100 * $this->vat), 2);
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
