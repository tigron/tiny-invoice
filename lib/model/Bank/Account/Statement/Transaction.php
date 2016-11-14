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
	 * Get balance
	 *
	 * @access public
	 */
	public function get_balance() {
		$balances = Bank_Account_Statement_Transaction_Balance::get_by_bank_account_statement_transaction($this);
		$sum = $this->amount;
		foreach ($balances as $balance) {
			$sum -= $balance->amount;
		}
		return $sum;
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
	 * Link invoice
	 *
	 * @access public
	 * @param Invoice $invoice
	 * @param decimal $amount
	 */
	public function link_invoice(Invoice $invoice, $amount = null) {
		if ($amount === null) {
			$amount = $invoice->get_price_incl();
		}
		if (bcsub($this->get_balance(), $amount, 3) < 0) {
			throw new Exception('Cannot link for amount ' . $amount . ', balance is ' . $this->get_balance());
		}

		$balance = new Bank_Account_Statement_Transaction_Balance();
		$balance->bank_account_statement_transaction_id = $this->id;
		$balance->set_linked_object($invoice);
		$balance->amount = $amount;
		$balance->save();

		if ($this->get_balance() == 0) {
			$this->balanced = true;
			$this->save();
		}

		$transfer = new Transfer();
		$transfer->created = $this->date;
		$transfer->type = TRANSFER_TYPE_PAYMENT_WIRETRANSFER;
		$transfer->amount = $amount;
		$transfer->bank_account_statement_transaction_id = $this->id;
		$transfer->save();

		$invoice->add_transfer($transfer);
	}

	/**
	 * Automatic link
	 *
	 * @access public
	 */
	public function automatic_link() {
		if ($this->amount > 0) {
			$this->automatic_link_invoice();
		} else {
			throw new Exception('Not implemented yet');
		}
	}

	/**
	 * Automatic link with invoice
	 *
	 * @access public
	 */
	private function automatic_link_invoice() {
		preg_match("/\+\+\+(\d{3}\/\d{4}\/\d{5})\+\+\+/", $this->get_message(), $output_array);
		if (isset($output_array[1])) {
			$ogm = str_replace('/', '', $output_array[1]);
			$id = substr($ogm, 0, -2);
			$invoice = Invoice::get_by_id($id);
			if (bccomp($invoice->get_price_incl(), $this->amount, 3) == 0) {
				$this->link_invoice($invoice);
				return;
			}
		}
		throw new Exception('No invoice found for this message');
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
	 * Get unbalanced
	 *
	 * @access public
	 * @return array $bank_account_statements
	 */
	public static function get_unbalanced() {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM bank_account_statement_transaction WHERE balanced=0', [ ]);

		$transactions = [];
		foreach ($ids as $id) {
			$transactions[] = self::get_by_id($id);
		}
		return $transactions;
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
