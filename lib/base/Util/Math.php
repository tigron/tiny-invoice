<?php
/**
 * Util class
 *
 * Contains utilities for calculations
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Util_Math {

	/**
	 * Add
	 *
	 * @access public
	 * @param string $param1
	 * @param string $param2
	 */
	public static function add($param1, $param2) {
		bcscale(2);
		return bcadd($param1, $param2);
	}

	/**
	 * Sub
	 *
	 * @access public
	 * @param string $param1
	 * @param string $param2
	 * @return float $result
	 */
	public static function sub($param1, $param2) {
		bcscale(2);
		return bcsub($param1, $param2);
	}

	/**
	 * Mul
	 *
	 * @access public
	 * @param string $param1
	 * @param string $param2
	 * @return string $result
	 */
	public static function mul($param1, $param2) {
		bcscale(2);
		return bcmul($param1, $param2);
	}

	/**
	 * Efficiently calculates how many digits the integer portion of a number has.
	 *
	 * @access public
	 * @param int $number
	 * @return int $digits
	 */
	public static function number_of_digits($number) {
		$log = log10($number);

		if ($log < 0) {
			return 1;
		} else {
			return floor($log) + 1;
		}
	}

	/**
	 * Formats a number to a minimum amount of digits.
	 * In other words, makes sure that a number has at least $digits on it, even if
	 * that means introducing redundant decimal zeroes at the end, or rounding the
	 * ones present exceeding the $digits count when combined with the integers.
	 * This is primarily useful for generating human-friendly numbers.
	 *
	 * @access public
	 * @param double $value
	 * @param bool $round
	 * @param int $digits
	 */
	public static function limit_digits($value, $round = true, $digits = 3) {
		$integers = floor($value);

		$decimals_needed = $digits - self::number_of_digits($integers);

		if ($decimals_needed < 1) {
			return $integers;
		} else {
			if ($round) {
				$parts = explode('.', round($value, $decimals_needed));
				$integers = $parts[0];
			} else {
				$parts = explode('.', $value);
			}

			if (isset($parts[1])) {
				$decimals = $parts[1];
			} else {
				$decimals = 0;
			}

			$joined = $integers . '.' . $decimals . str_repeat('0', $digits);

			return substr($joined, 0, $digits + 1);
		}
	}
}
