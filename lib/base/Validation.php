<?php
/**
 * Validation Class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Validation {

	/**
	 * Validate an email address
	 *
	 * @access public
	 * @param string $email
	 * @return bool
	 */
	public static function validate_email($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Validate a phone number
	 *
	 * @access public
	 * @param string $phone
	 * @return bool
	 */
	public static function validate_phone($phone) {
		if (preg_match('/^\+[0-9]{1,3}\.[0-9]{5,20}$/', $phone) === 1) {
			return true;
		}

		return false;
	}

	/**
	 * Validate a date
	 *
	 * @access public
	 * @param string $date
	 * @return bool
	 */
	public static function validate_date($date) {
		$date_parts = explode('-', $date);

		if (count($date_parts) <> 3) {
			return false;
		}

		return checkdate($date_parts[0], $date_parts[1], $date_parts[2]);
	}

	/**
	 * Validate a VAT number
	 *
	 * @access public
	 * @return bool
	 * @param string $vat
	 * @param Country $country
	 */
	public static function validate_vat($vat, Country $country) {
		return Vat_Check::check($vat, $country);
	}
}
