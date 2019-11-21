<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Util {

	/**
	 * Random code generator
	 *
	 * @access public
	 * @param string $length
	 * @param string $chars
	 * @return string $code
	 */
	public static function create_random_code($length, $chars = '23456789ABCDEFGHKMNPQRSTWXYZ') {
		$code = '';

		for ($i = 1; $i <= $length; $i++) {
			$num = mt_rand(1, strlen($chars));
	        $tmp = substr($chars, $num-1, 1);
	        $code = $code . $tmp;
		}

		return $code;
	}

	/**
	 * Call
	 *
	 * @access public
	 * @param string $method
	 * @param array $arguments
	 */
	 public static function __callstatic($method, $arguments) {
 		list($classname, $method) = explode('_', $method, 2);
 		$class = ucfirst($classname) . '.php';
 		$classname = 'Util_' . $classname;

 		if (!method_exists($classname, $method)) {
 			throw new Exception('method ' . $method . ' does not exists');
 		}

 		$result = forward_static_call_array(array($classname, $method), $arguments);

 		return $result;
 	}
}
