<?php
/**
 * Bank_Account_Statement_Transaction class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Bank_Account_Statement_Transaction {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Get message
	 *
	 * @access public
	 * @return string $message
	 */
	public function get_message() {
		if (!empty($this->message)) {
			return $this->message;
		}
		if (empty($this->message) and empty($this->structured_message)) {
			return '';
		}
		$message = '+++' . substr($this->structured_message, 0, 3) . '/' . substr($this->structured_message, 3, 4) . '/' . substr($this->structured_message, 7, 5) . '+++';
		return $message;
	}

	/**
	 * Get bank_account_statement_transaction_balances
	 *
	 * @access public
	 * @return array $balances
	 */
	public function get_bank_account_statement_transaction_balances() {
		return Bank_Account_Statement_Transaction_Balance::get_by_bank_account_statement_transaction($this);
	}

	/**
	 * Get by bank_account_statement sequence_number
	 *
	 * @access public
	 * @param Bank_Account_Statement $bank_account_statement
	 * @param string $sequence
	 * @return Bank_Account_Statement $bank_account_statement
	 */
	public static function get_by_bank_account_statement_sequence(Bank_Account_Statement $bank_account_statement, $sequence) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM bank_account_statement_transaction WHERE bank_account_statement_id=? AND sequence=?', [ $bank_account_statement->id, $sequence ]);
		if ($id === null) {
			throw new Exception('Bank account statement transaction for bank account statement ' . $bank_account_statement->identifier . ' and sequence ' . $sequence . ' not found');
		}
		return self::get_by_id($id);
	}

	/**
	 * Get by bank_account_statement
	 *
	 * @access public
	 * @param Bank_Account_Statement $bank_account_statement
	 * @return array $bank_account_statements
	 */
	public static function get_by_bank_account_statement(Bank_Account_Statement $bank_account_statement) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM bank_account_statement_transaction WHERE bank_account_statement_id=?', [ $bank_account_statement->id ]);

		$transactions = [];
		foreach ($ids as $id) {
			$transactions[] = self::get_by_id($id);
		}
		return $transactions;
	}
}
