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
	 * Get by identifier
	 *
	 * @access public
	 * @param string $identifier
	 * @return Bank_Account $bank_account
	 */
	public static function get_by_identifier($identifier) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM bank_account WHERE identifier=?', [ $identifier ]);
		if ($id === null) {
			throw new Exception('Bank account with identifier ' . $identifier . ' not found');
		}
		return self::get_by_id($id);
	}
}
