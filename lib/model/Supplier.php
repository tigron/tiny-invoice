<?php
/**
 * Supplier class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;
use \Iban\Validation\Validator;

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
			$iban_validator = new Validator();
			if (!$iban_validator->validate($this->details['iban'])) {
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

	/**
	 * Get by IBAN
	 *
	 * @access public
	 * @param string $iban
	 * @return array $suppliers
	 */
	public static function get_by_iban($iban) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM supplier WHERE iban=?', [ $iban ]);
		$suppliers = [];
		foreach ($ids as $id) {
			$suppliers[] = self::get_by_id($id);
		}
		return $suppliers;
	}

	/**
	 * Get by accounting_identifier
	 *
	 * @access public
	 * @param string $accounting_identifier
	 * @return Supplier $supplier
	 */
	public static function get_by_accounting_identifier($accounting_identifier) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM supplier WHERE accounting_identifier=?', [ $accounting_identifier ]);
		$suppliers = [];
		foreach ($ids as $id) {
			$suppliers[] = self::get_by_id($id);
		}
		return $suppliers;
	}
}
