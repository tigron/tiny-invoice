<?php
/**
 * Transfer class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

define('TRANSFER_TYPE_PAYMENT_MANUAL',       1);
define('TRANSFER_TYPE_PAYMENT_CREDITNOTE',   2);
define('TRANSFER_TYPE_PAYMENT_WIRETRANSFER', 10);

use \Skeleton\Database\Database;

class Transfer {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete {
		delete as trait_delete;
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$this->trait_delete();
		Log::create('Transfer removed', $this->invoice);
	}

	/**
	 * Get transfers by invoice
	 *
	 * @access public
	 * @param Invoice $invoice
	 * @return array Transfer $items
	 */
	public static function get_by_invoice(Invoice $invoice) {
		$table = self::trait_get_database_table();
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE invoice_id = ?', [ $invoice->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get transfer amount by invoice
	 *
	 * @access public
	 * @param Invoice $invoice
	 * @return descimal $amount
	 */
	public static function get_amount_by_invoice(Invoice $invoice) {
		$table = self::trait_get_database_table();
		$db = Database::get();
		$amount = $db->get_one('SELECT SUM(amount) FROM ' . $table . ' WHERE invoice_id = ?', [ $invoice->id ]);
		return $amount;
	}

	/**
	 * Get by Bank_Account_Statement_Transaction_Balance
	 *
	 * @access public
	 * @param Bank_Account_Statement_Transaction_Balance $bank_account_statement_transaction_balance
	 * @return array $balances
	 */
	public static function get_by_bank_account_statement_transaction_balance(Bank_Account_Statement_Transaction_Balance $bank_account_statement_transaction_balance) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM transfer WHERE bank_account_statement_transaction_balance_id=?', [ $bank_account_statement_transaction_balance->id ]);
		if ($id === null) {
			throw new Exception('No transfers found for this balance');
		}
		return self::get_by_id($id);

	}
}
