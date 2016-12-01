<?php
/**
 * Bank_Account_Statement_Transaction class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Bank_Account_Statement_Transaction_Balance {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Set linked object
	 *
	 * @access public
	 * @param Object $object
	 */
	public function set_linked_object($object) {
		$this->linked_object_classname = get_class($object);
		$this->linked_object_id = $object->id;
	}

	/**
	 * Get by bank_account_statement sequence_number
	 *
	 * @access public
	 * @param Bank_Account_Statement $bank_account_statement
	 * @param string $sequence_number
	 * @return Bank_Account_Statement $bank_account_statement
	 */
	public static function get_by_bank_account_statement_transaction(Bank_Account_Statement_Transaction $bank_account_statement_transaction) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM bank_account_statement_transaction_balance WHERE bank_account_statement_transaction_id=?', [ $bank_account_statement_transaction->id ]);
		$balances = [];
		foreach ($ids as $id) {
			$balances[] = self::get_by_id($id);
		}
		return $balances;
	}
}
