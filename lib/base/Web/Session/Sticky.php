<?php
/**
 * Session class to control the session
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @version $Id$
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
		if (!isset($_SESSION['system'])) {
			$_SESSION['system'] = [];
		}
		$_SESSION['system'][$key] = ['counter' => 0, 'data' => $value];
	}

	/**
	 * Get
	 *
	 * @access public
	 * @param string $key
	 * @param bool $remove_after_get
	 */
	public function __get($key) {
		if (!isset($_SESSION['system'][$key])) {
			throw new Exception('Key not found');
		}
		return $_SESSION['system'][$key]['data'];
	}

	/**
	 * Isset
	 *
	 * @access public
	 * @param string $key
	 */
	public function __isset($key) {
		if (!isset($_SESSION['system'][$key])) {
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
		unset($_SESSION['system'][$key]);
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

	/**
	 * Sticky clear
	 *
	 * @access public
	 * @param string $module
	 */
	public static function clear() {
		if (!isset($_SESSION['system'])) {
			return;
		}

		foreach ($_SESSION['system'] as $key => $variables) {
			if (isset($variables['counter']) AND $variables['counter'] < 1) {
				$_SESSION['system'][$key]['counter']++;
				continue;
			}
			unset($_SESSION['system'][$key]);
		}
	}
}
