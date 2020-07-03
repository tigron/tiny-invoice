<?php
/**
 * Bookkeeping_Account class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Bookkeeping_Account {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Validate
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = null) {
		$errors = [];
		$required_fields = [ 'number' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (isset($this->details['number'])) {
			try {
				$account = self::get_by_number($this->number);
				if ($account->id == $this->id) {
					throw new Exception();
				}
				$errors['number'] = 'unique';
			} catch (Exception $e) {
			}
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get by number
	 *
	 * @access public
	 * @param string $number
	 * @return Bookkeeping_Account $account
	 */
	public static function get_by_number($number) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM bookkeeping_account WHERE number=?', [ $number ]);
		if ($id === null) {
			throw new Exception('Bookkeeping_Account not found');
		}
		return self::get_by_id($id);
	}
}
