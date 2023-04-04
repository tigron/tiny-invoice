<?php
/**
 * Bank_Account class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Bank_Account {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Get bank_account_statements
	 *
	 * @access public
	 * @return array $bank_account_statements
	 */
	public function get_bank_account_statements() {
		return Bank_Account_Statement::get_by_bank_account($this);
	}

	/**
	 * Has bank_account_statements
	 *
	 * @access public
	 * @return bool $has_bank_account_statements
	 */
	public function has_bank_account_statements() {
		try {
			$this->get_last_bank_account_statement();
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Validate
	 *
	 * @access public
	 * @param array &$errors
	 * @return bool $validated
	 */
	public function validate(&$errors = null) {
		$errors = [];
		$required_fields = [ 'number', 'name' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		try {
			$user = self::get_by_number($this->details['number']);
			if ($this->id === null OR ($this->id !== null AND $this->id != $user->id)) {
				$errors['number'] = 'already exists';
			}
		} catch (Exception $e) { }

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get last bank_account_statement
	 *
	 * @access public
	 * @return Bank_Account_Statement $bank_account_statement
	 */
	public function get_last_bank_account_statement() {
		return Bank_Account_Statement::get_last_by_bank_account($this);
	}

	/**
	 * Get statements by year
	 *
	 * @access public
	 * @param string $year
	 */
	public function get_bank_account_statements_by_year($year) {
		return Bank_Account_Statement::get_by_bank_account_year($this, $year);
	}

	/**
	 * Get by identifier
	 *
	 * @access public
	 * @param string $number
	 * @return Bank_Account $bank_account
	 */
	public static function get_by_number($number) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM bank_account WHERE number=?', [ $number ]);
		if ($id === null) {
			throw new Exception('Bank account with number ' . $number . ' not found');
		}
		return self::get_by_id($id);
	}
	
	/**
	 * Get by identifier
	 *
	 * @access public
	 * @return Bank_Account[] $bank_accounts
	 */
	public static function get_all() {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM bank_account WHERE archived IS NULL');

		$items = [];
		foreach($ids as $id){
			$items[] = self::get_by_id($id);
		}
		
		return $items;
	}
}
