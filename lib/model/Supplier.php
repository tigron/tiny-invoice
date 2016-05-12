<?php
/**
 * Supplier class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;
use IBAN\Validation\IBANValidator;

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
		$required_fields = [ 'company' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (!empty($this->details['vat'])) {
			if (!Validation::validate_vat($this->details['vat'], $this->country)) {
				$errors['vat'] = 'incorrect';
			}
		}

		if (!empty($this->details['iban'])) {
			$ibanValidator = new IBANValidator();
			if (!$ibanValidator->validate($this->details['iban'])) {
				$errors['iban'] = 'incorrect';
			}
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

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


}
