<?php
/**
 * Customer class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Customer {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Validate user data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = []) {
		$required_fields = [ 'firstname', 'lastname', 'email', 'street', 'housenumber', 'city', 'zipcode', 'country_id' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (isset($this->details['email']) AND !Validation::validate_email($this->details['email'])) {
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
	 * Get display_name
	 *
	 * @access public
	 * @return string $display_name
	 */
	public function get_display_name() {
		$display_name = '';
		if (!empty($this->details['company'])) {
			$display_name .= $this->details['company'];

			if (!empty($this->details['firstname']) or !empty($this->details['lastname'])) {
				$display_name .= ' (' . $this->details['firstname'] . ' ' . $this->details['lastname'] . ')';
			}

			return $display_name;
		}

		return $this->details['firstname'] . ' ' . $this->details['lastname'];
	}

	/**
	 * Get customer indentifier
	 *
	 * @access public
	 * @return string $identifier
	 */
	public function get_identifier() {
		try {
			$setting = Setting::get_by_name('customer_identifier')->value;
		} catch (Exception $e) {
			$setting = '%d';
		}
		return sprintf($setting, $this->id);
	}

	/**
	 * Get active invoice contacts
	 *
	 * @access public
	 * @return array Customer_Contact $items
	 */
	public function get_active_customer_contacts() {
		return Customer_Contact::get_active_by_customer($this);
	}

	/**
	 * Get invoice contacts
	 *
	 * @access public
	 * @return array Customer_Contact $items
	 */
	public function get_customer_contacts() {
		return Customer_Contact::get_by_customer($this);
	}

}
