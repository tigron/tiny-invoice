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
	use \Skeleton\Object\Delete {
		delete as trait_delete;
	}
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
	 * Get linked object
	 *
	 * @access public
	 * @return Object $object
	 */
	public function get_linked_object() {
		$classname = $this->linked_object_classname;
		return $classname::get_by_id($this->linked_object_id);
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$this->trait_delete();

		$object = $this->get_linked_object();

		if (get_class($object) == 'Document_Incoming_Invoice' or get_class($object) == 'Document_Incoming_Creditnote') {
			if ($object->get_balance() == 0) {
				$object->balanced = true;
			} else {
				$object->balanced =false;
			}
			$object->save();
		} elseif (get_class($object) == 'Invoice') {
			try {
				$transfer = Transfer::get_by_bank_account_statement_transaction_balance($this);
				$transfer->delete();
			} catch (Exception $e) { }
			$object->check_paid();
		}

		$transaction = $this->bank_account_statement_transaction;

		if ($transaction->get_balance() == 0) {
			$transaction->balanced = true;
		} else {
			$transaction->balanced = false;
		}
		$transaction->save();
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

	/**
	 * Get by linked object
	 *
	 * @access public
	 * @param Object $linked_object
	 * @return array $balances
	 */
	public static function get_by_linked_object($linked_object) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM bank_account_statement_transaction_balance WHERE linked_object_classname=? AND linked_object_id=?', [ get_class($linked_object), $linked_object->id ]);
		$balances = [];
		foreach ($ids as $id) {
			$balances[] = self::get_by_id($id);
		}
		return $balances;
	}
}
