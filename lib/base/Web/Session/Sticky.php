<?php
/**
 * Session class to control the session
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Web_Session_Sticky {

	/**
	 * Session object
	 *
	 * @access private
	 * @var Web_Session $session
	 */
	private static $sticky_session = null;

	/**
	 * Module
	 *
	 * @var string $module
	 * @access private
	 */
	public $module = null;

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
	 * Set
	 *
	 * @access public
	 * @param string $key
	 * @param string $value
	 */
	public function __set($key, $value) {
		if ($this->module === null) {
			throw new Exception('module not set');
		}

		if (!isset($_SESSION['system'][$this->module])) {
			$_SESSION['system'][$this->module] = array();
		}
		$_SESSION['system'][$this->module][$key] = $value;
	}

	/**
	 * Get
	 *
	 * @access public
	 * @param string $key
	 * @param bool $remove_after_get
	 */
	public function __get($key) {
		if ($this->module === null) {
			throw new Exception('module not set');
		}
		if (!isset($_SESSION['system'][$this->module][$key])) {
			throw new Exception('Key not found');
		}
		return $_SESSION['system'][$this->module][$key];
	}

	/**
	 * Isset
	 *
	 * @access public
	 * @param string $key
	 */
	public function __isset($key) {
		if ($this->module === null) {
			throw new Exception('module not set');
		}
		if (!isset($_SESSION['system'][$this->module][$key])) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Unset
	 *
	 * @access public
	 * @param string $key
	 */
	public function __unset($key) {
		if ($this->module === null) {
			throw new Exception('module not set');
		}
		unset($_SESSION['system'][$this->module][$key]);
	}

	/**
	 * Get a Session object
	 *
	 * @access public
	 * @return Session
	 */
	public static function get() {
		if (self::$sticky_session === null) {
			self::$sticky_session = new Web_Session_Sticky();
		}
		return self::$sticky_session;
	}
}
