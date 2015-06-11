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
	        $tmp = substr($chars, $num, 1);
	        $code = $code . $tmp;
		}

		return $code;
	}

	/**
	 * Sanitize strings to ascii-only URL safe strings
	 *
	 * @access public
	 * @param string $string The string to sanitize
	 * @return string
	 */
	public static function sanitize_url($string) {
		$string = strtolower($string);
		$string = self::sanitize_filename($string);

		$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
		$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
		$string = str_replace($search, $replace, $string);

		$string = preg_replace('/[^(\x20-\x7F)]*/','', $string);
	    $string = str_replace('_', '', $string);
	    $string = str_replace('-', '', $string);
	    $string = str_replace('.', '', $string);

		return $string;
	}

	/**
	 * Check if a directory is empty
	 *
	 * @access public
	 * @param string $directory
	 * @return bool $exists
	 */
	public static function is_dir_empty($dir) {
		if (!is_readable($dir)) {
			return null;
		}

		$handle = opendir($dir);
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				return false;
			}
		}

		return true;
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

		$classname = ucfirst($classname);
		$filename = LIB_PATH . '/base/Util/' . $classname . '.php';

		if (file_exists($filename)) {
			require_once $filename;
		} else {
			throw new Exception('File does not exist: ' . $filename);
		}

		$classname = 'Util_' . $classname;

		if (!method_exists($classname, $method)) {
			throw new Exception('Method ' . $method . ' does not exists, in autoloaded class ' . $classname);
		}

		$result = forward_static_call_array(array($classname, $method), $arguments);
		return $result;
	}
}
