<?php
/**
 * Customer_Contact class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Customer_Contact {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Get VAT formatted
	 *
	 * @access public
	 * @return string $vat
	 */
	public function get_vat_formatted() {
		if (!isset($this->details['vat']) OR $this->details['vat'] == '') {
			return '';
		}

		if ($this->country->iso2 == 'BE') {
			return 'BE ' . substr($this->vat, 0, 4) . '.' . substr($this->vat, 4, 3) . '.' . substr($this->vat, 7);
		} else {
			return $this->country->iso2 . ' ' . $this->vat;
		}
	}

	/**
	 * Validate user data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = array()) {
		$required_fields = array('firstname', 'lastname', 'email', 'street', 'housenumber', 'city', 'zipcode');
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (!Validation::validate_email($this->details['email'])) {
			$errors['email'] = 'syntax error';
		}

		if (isset($this->details['vat']) AND $this->details['vat'] != '') {
			if (!Validation::validate_vat($this->details['vat'], $this->country)) {
				$errors['vat'] = 'incorrect';
			}
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check if this contact is VAT bound
	 *
	 * @return bool
	 * @access public
	 */
	public function vat_bound() {
		if (isset($this->details['vat_bound']) AND $this->vat_bound == 0) {
			return false;
		} elseif (isset($this->details['vat_bound']) AND $this->vat_bound == 1) {
			return true;
		}  else {
			/* If the customer:
			 *  - Is a European resident
			 *  - Not a Belgian citizen
			 *  - Has a VAT number
			 * he is not bound to VAT
			 */
			if ($this->country->european == true && $this->country->iso2 != 'BE' && $this->vat != '') {
				return false;
			}

			/* If the customer is not a European resident, he is not VAT bound */
			else if ($this->country->european == false) {
				return false;
			}

			/* If the customer is anything else, he is VAT bound */
			else {
				return true;
			}
		}
	}

	/**
	 * Get Vat Rate Country
	 *
	 * @access public
	 * @param Vat_Rate $vat_rate
	 * @return Vat_Rate_Country $vat_rate_country
	 */
	public function get_vat_rate_country(Vat_Rate $vat_rate) {
		if (isset($this->details['vat_bound']) AND $this->vat_bound == 0) {
			throw new Exception('No Vat_Rate_Country');
		} elseif (isset($this->details['vat_bound']) AND $this->vat_bound == 1) {
			return Vat_Rate_Country::get_by_vat_rate_country($vat_rate, $this->country);
		}  else {
			/* If the customer:
			 *  - Is a European resident
			 *  - Not a Belgian citizen
			 *  - Has a VAT number
			 * he is not bound to VAT
			 */
			if ($this->country->european == true && $this->country->iso2 != 'BE' && $this->vat != '') {
				throw new Exception('No Vat_Rate_Country');
			}

			/* If the customer is not a European resident, he is not VAT bound */
			else if ($this->country->european == false) {
				throw new Exception('No Vat_Rate_Country');
			}

			/* If the customer is anything else, he is VAT bound */
			else {
				return Vat_Rate_Country::get_by_vat_rate_country($vat_rate, $this->country);
			}
		}
	}

	/**
	 * Get VAT
	 *
	 * @access public
	 * @return double $vat
	 */
	public function get_vat(Vat_Rate $vat_rate) {
		try {
			$vat_rate_country = $this->get_vat_rate_country($vat_rate);
			return $vat_rate_country->vat;
		} catch (Exception $e) {
			return 0;
		}
	}

	/**
	 * Get active by Customer
	 *
	 * @access public
	 * @param Customer $customer
	 * @return array Customer_Contact $items
	 */
	public static function get_active_by_customer(Customer $customer) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE customer_id = ? AND active = 1 ORDER BY created DESC', [ $customer->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get by Customer
	 *
	 * @access public
	 * @param Customer $customer
	 * @return array Customer_Contact $items
	 */
	public static function get_by_customer(Customer $customer) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE customer_id = ? ORDER BY created DESC', [ $customer->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}
}
