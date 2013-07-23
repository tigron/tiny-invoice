<?php
/**
 * Util_Validate class
 *
 * Contains validation utils
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Util_Validate {

	/**
	 * Validate an email address
	 *
	 * @access public
	 * @param string $email
	 * @return bool
	 */
	public static function email($email) {
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Validate a phone number
	 *
	 * @access public
	 * @param string $phone
	 * @return bool
	 */
	public static function phone($phone) {
		$config = Config::Get();
		$phone_regexp = $config->regexp_phone;
		if (!ereg($phone_regexp, $phone)) {
			return false;
		} else {
			return true;
		}
	}
}
