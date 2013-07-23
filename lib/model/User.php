<?php
/**
 * User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

require_once LIB_PATH . '/model/Country.php';
require_once LIB_PATH . '/model/Log.php';
require_once LIB_PATH . '/model/Language.php';

class User {
	use Model, Get, Save, Delete, Page;

	/**
	 * @var User $user
	 * @access private
	 */
	private static $user = null;

	/**
	 * Set password
	 *
	 * @access public
	 * @param string $password
	 */
	public function set_password($password) {
		$this->password = sha1($password);
	}

	/**
	 * Fetch a user by username
	 *
	 * @access public
	 * @param string $username
	 * @return User $user
	 */
	public static function get_by_username($username) {
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM user WHERE username = ?', array($username));

		if ($id === null) {
			throw new Exception('User not found');
		}

		return User::get_by_id($id);
	}

	/**
	 * Fetch a user by email
	 *
	 * @access public
	 * @param string $email
	 * @return User $user
	 */
	public static function get_by_email($email) {
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM user WHERE email = ?', array($email));

		if ($id === null) {
			throw new Exception('User not found');
		}

		return User::get_by_id($id);
	}

	/**
	 * Authenticate a user
	 *
	 * @access public
	 * @throws Exception
	 * @param string $username
	 * @param string $password
	 * @return User $user
	 */
	public static function authenticate($username, $password) {
		$user = User::get_by_username($username);

		if ($user->password != sha1($password)) {
			throw new Exception('Authentication failed');
		}

		return $user;
	}

	/**
	 * Get the current user
	 *
	 * @access public
	 * @return User $user
	 */
	public static function get() {
		if (self::$user !== null) {
			return self::$user;
		}

		throw new Exception('No user set');
	}

	/**
	 * Set the current user
	 *
	 * @access public
	 * @param User $user
	 */
	public static function set(User $user) {
		self::$user = $user;
	}
}
