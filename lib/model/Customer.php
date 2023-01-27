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
	use \Skeleton\Object\Uuid;
	use \Skeleton\Object\Save;
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
	public function validate(&$errors = null) {
		$errors = [];
		$required_fields = [ 'firstname', 'lastname', 'email', 'street', 'housenumber', 'city', 'zipcode', 'country_id' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}


		// If no country is set, uselesss to try and validate phone numbers
		if(isset($this->details['country_id'])) {
			$country = \Country::get_by_id($this->details['country_id']);

			// Validate the phone_numbers
			$phone_util = \libphonenumber\PhoneNumberUtil::getInstance();
			if (isset($this->details['phone'])) { // Can be validated on this field when using the lib validator
				try {
					$number_object = $phone_util->parse($this->details['phone'], $country->iso2);
					$is_valid = $phone_util->isValidNumber($number_object);
				} catch (\Exception $e) {
					$is_valid = false;
				}
				if(!$is_valid){
					$errors['phone'] = 'syntax error';
				}
			}

			if (isset($this->details['mobile'])) { // Can be validated on this field when using the lib validator
				try {
					$number_object = $phone_util->parse($this->details['mobile'], $country->iso2);
					$is_valid = $phone_util->isValidNumber($number_object);
				} catch (\Exception $e) {
					$is_valid = false;
				}
				if(!$is_valid){
					$errors['mobile'] = 'syntax error';
				}
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

	/**
	 * Create first customer_contact
	 *
	 * @access public
	 * @return Customer_Contact $customer_contact
	 */
	public function create_first_customer_contact() {
		$customer_contact = new Customer_Contact();
		$customer_contact->company = $this->company;
		$customer_contact->customer_id = $this->id;
		$customer_contact->firstname = $this->firstname;
		$customer_contact->lastname = $this->lastname;
		$customer_contact->email = $this->email;
		$customer_contact->street = $this->street;
		$customer_contact->housenumber = $this->housenumber;
		$customer_contact->city = $this->city;
		$customer_contact->zipcode = $this->zipcode;
		$customer_contact->country_id = $this->country_id;
		$customer_contact->phone = $this->phone;
		$customer_contact->mobile = $this->mobile;
		$customer_contact->fax = $this->fax;
		$customer_contact->email = $this->email;
		$customer_contact->vat = $this->vat;
		$customer_contact->language_id = $this->language_id;
		$customer_contact->active = true;
		$customer_contact->save();

		return $customer_contact;
	}

	/**
	 * Get by uuid
	 *
	 * @access public
	 * @param string $uuid
	 * @return Customer $customer
	 */
	public static function get_by_uuid($uuid) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM customer WHERE uuid=?', [ $uuid ]);
		return self::get_by_id($id);
	}

	/**
	 * Get by Country vat
	 *
	 * @access public
	 * @param Country $country
	 * @param string $vat
	 * @return array $customers
	 */
	public static function get_by_country_vat(Country $country, $vat) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM customer WHERE country_id=? AND vat=?', [ $country->id, $vat ]);
		$customers = [];
		foreach ($ids as $id) {
			$customers[] = self::get_by_id($id);
		}
		return $customers;
	}
}
