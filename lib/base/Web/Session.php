<?php
/**
 * Session class to control the session
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Web_Session {
	/**
	 * Contructor
	 *
	 * @access private
	 * @param string $username
	 * @param string $password
	 */
	private function __construct() {
	}

	/**
	 * Get a field
	 *
	 * @access public
	 * @param string $field
	 * @return mixed
	 */
	public function __get($key) {
		if (!isset($_SESSION[$key])) {
			throw new Exception('unknown key ' . $key);
		}
		return $_SESSION[$key];
	}

	/**
	 * Set a key
	 *
	 * @access public
	 * @param string $key
	 * @param mixes $value
	 */
	public function __set($key, $value) {
		$_SESSION[$key] = $value;
	}

	/**
	 * Check if a variable exists
	 *
	 * @access public
	 * @return bool
	 */
	public function exists($key) {
		if (isset($_SESSION[$key])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get a Session object
	 *
	 * @access public
	 * @return Session
	 */
	public static function get() {
		return new Web_Session();
	}

	/**
	 * Set an item in the Session object
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public static function set($key, $value) {
		$session = Web_Session();
		$session->$key = $value;
	}

	/**
	 * Set an item in the Session object
	 *
	 * @access public
	 * @param string $key
	 */
	public static function delete($key) {
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
	}

	/**
	 * Redirect
	 *
	 * @access public
	 * @param string $url
	 */
	public static function redirect($url) {
		if (isset($_SESSION['is_ajax']) AND $_SESSION['is_ajax'] === true) {
			return;
		}

		if ($url[0] == '/') {
			$url = substr($url, 1);
		}

		try {
			$url = Util::rewrite_reverse_link($url);
		} catch (Exception $e) { }

		header('Location: /'.$url);
		echo 'Redirecting to : '.$url;
		exit;
	}

	/**
	 * Start
	 *
	 * @access public
	 */
	public static function start() {
		session_name('APP');
		session_start();
	}

	/**
	 * Start
	 *
	 * @access public
	 */
	public static function destroy() {
		session_destroy();
		session_start();
	}
}
