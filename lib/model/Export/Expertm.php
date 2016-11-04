<?php
/**
 * Export_Expertm_User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

abstract class Export_Expertm extends Export {

	/**
	 * Numeric
	 *
	 * @access protected
	 * @param int $size
	 * @param string $field
	 */
	protected function num($size, $field) {
		return str_pad($field, $size, '0', STR_PAD_LEFT);
	}

	/**
	 * Alfa-Numeric
	 *
	 * @access protected
	 * @param int $size
	 * @param string $field
	 */
	protected function alf($size, $field) {
		$field = iconv("UTF-8", "ASCII//TRANSLIT", $field);
		return substr(str_pad($field, $size, ' '), 0, $size);
	}

	/**
	 * Currency
	 *
	 * @access protected
	 * @param int $size
	 * @param string $val
	 */
	protected function cur($size, $val) {
		$val = sprintf('%0'.($size-5).'.4f', $val);
		$valarr = explode('.', $val);
		$val = str_pad($valarr[0], $size-5, '0', STR_PAD_LEFT);
		$val .= str_pad($valarr[1], 4, '0', STR_PAD_RIGHT).'+';
		$val = str_replace('.', '', $val);
		return $val;
	}
}
