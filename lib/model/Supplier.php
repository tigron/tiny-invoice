<?php
/**
 * Supplier class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Supplier {
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
		$required_fields = [ 'company', 'vat' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
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

}
