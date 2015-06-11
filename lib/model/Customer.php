<?php
/**
 * Customer class
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Customer {
	use Model, Get, Save, Delete, Page;

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

		if (isset($this->details['email']) AND !Util::validate_email($this->details['email'])) {
			$errors['email'] = 'syntax error';
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
