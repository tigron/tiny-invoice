<?php
/**
 * User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class User {
	use Model, Get, Save, Delete, Page;

	/**
	 * @var User $user
	 * @access private
	 */
	private static $user = null;

	/**
	 * Validate user data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = array()) {
		$required_fields = array('username', 'password', 'firstname', 'lastname', 'email');
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (!Util::validate_email($this->details['email'])) {
			$errors['email'] = 'syntax error';
		}

		if ($this->id === null) {
			try {
				$user = self::get_by_username($this->details['username']);
				$errors['username'] = 'already exists';
			} catch (Exception $e) { }
		}

		if ($this->id === null) {
			try {
				$user = self::get_by_email($this->details['email']);
				$errors['email'] = 'already exists';
			} catch (Exception $e) { }
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Set password
	 *
	 * @access public
	 * @param string $password
	 */
	public function set_password($password) {
		if ($password == '') {
			return;
		}
		$this->details['password'] = sha1($password);
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
