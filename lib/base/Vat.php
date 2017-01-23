<?php
/**
 * Vat Class
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Vat {

	/**
	 * Format
	 *
	 * @access public
	 * @param string $email
	 * @return bool
	 */
	public static function format($vat, Country $country) {
		if ($vat == '') {
			return '';
		}

		if ($country->iso2 == 'BE') {
			return 'BE ' . substr($vat, 0, 4) . '.' . substr($vat, 4, 3) . '.' . substr($vat, 7);
		} else {
			return $country->iso2 . ' ' . $vat;
		}
	}

}
