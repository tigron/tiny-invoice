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
	 * Get active invoice contacts
	 *
	 * @access public
	 * @return array Invoice_Contact $items
	 */
	public function get_active_invoice_contacts() {
		return Invoice_Contact::get_active_by_customer($this);
	}

}
