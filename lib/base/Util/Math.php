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
}
