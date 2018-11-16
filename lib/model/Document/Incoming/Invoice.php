<?php
/**
 * Document_Incoming_Invoice class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Document_Incoming_Invoice extends Document {
	use \Skeleton\Object\Child;

	/**
	 * Change classname
	 *
	 * @access public
	 * @param string $classname
	 */
	public function change_classname($classname) {
		if ($classname == $this->classname) {
			return $this;
		}
		$db = Database::get();
		$db->query('DELETE FROM document_incoming_invoice WHERE document_id=?', [ $this->id ]);
		return parent::change_classname($classname);
	}

	/**
	 * Get all transfers for this invoice
	 *
	 * @access public
	 * @return array $transfers
	 */
	public function get_transfers() {
		return Transfer::get_by_object($this);
	}

	/**
	 * Validate document data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors) {
		$errors = [];
		parent::validate($parent_errors);

		$required_fields = [ 'supplier_id', 'expiration_date', 'price_incl', 'price_excl' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->$required_field) OR $this->$required_field == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (!empty($this->payment_structured_message)) {
			$parts = explode('/', $this->payment_structured_message);
			if (count($parts) != 3) {
				$errors['payment_structured_message'] = 'incorrect';
			} elseif (strlen($parts[0]) != 3 OR strlen($parts[1]) != 4 OR strlen($parts[2]) != 5) {
				$errors['payment_structured_message'] = 'incorrect';
			} else {
				$number = $this->payment_structured_message;
				$number = str_replace('/', '', $number);
				$modulus = substr($number, -2);
				$number = substr($number, 0, 10);

				$number_modulus = $number % 97;
				if ($number_modulus == 0) {
					$number_modulus = 97;
				}

				if ($number_modulus != $modulus) {
					$errors['payment_structured_message'] = 'incorrect';
				}
			}
		}

		if (!empty($this->supplier_id) and !empty($this->supplier_identifier) and !empty($this->price_excl)) {
			$supplier = Supplier::get_by_id($this->supplier_id);
			$invoices = self::get_by_supplier_supplier_identifier($supplier, $this->supplier_identifier);
			foreach ($invoices as $key => $invoice) {
				if ($invoice->id == $this->id) {
					unset($invoices[$key]);
				}
				if ($invoice->price_excl != $this->price_excl) {
					unset($invoices[$key]);
				}
			}
			if (count($invoices) > 0) {
				$errors['supplier_identifier'] = 'unique';
			}
		}

		if (!empty($this->accounting_identifier)) {
			$invoices = self::get_by_accounting_identifier($this->accounting_identifier);
			foreach ($invoices as $key => $invoice) {
				if ($invoice->id == $this->id) {
					unset($invoices[$key]);
				}
			}
			if (count($invoices) > 0) {
				$errors['accounting_identifier'] = 'unique';
			}
		}

		if (isset($this->price_incl) AND isset($this->price_excl)) {
			if ($this->price_incl < $this->price_excl) {
				$errors['price_incl'] = 'incorrect';
			}
		}

		$errors = array_merge($errors, $parent_errors);

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get info
	 *
	 * @access public
	 * @return array $info
	 */
	public function get_info() {
		$info = parent::get_info();
		unset($info['id']);
		unset($info['document_id']);
		$info['supplier_id'] = $this->supplier->uuid;
		return $info;
	}

	/**
	 * Get transfer amount
	 *
	 * @access public
	 * @return double $amount
	 */
	public function get_transaction_amount() {
		$amount = 0;
		foreach ($this->get_bank_account_statement_transaction_balances() as $transaction) {
			$amount -= $transaction->amount;
		}
		return $amount;
	}

	/**
	 * Get bank account statement transaction balances
	 *
	 * @access public
	 * @return array $balances
	 */
	public function get_bank_account_statement_transaction_balances() {
		return Bank_Account_Statement_Transaction_Balance::get_by_linked_object($this);
	}

	/**
	 * Get balance
	 *
	 * @access public
	 */
	public function get_balance() {
		return $this->price_incl - $this->get_transaction_amount();
	}

	/**
	 * Get all ids
	 *
	 * @access public
	 * @return array $ids
	 */
	public function get_all_ids() {
		$db = Database::get();

		$public = false;
		try {
			$public = Setting::get_by_name('api_public_documents')->value;
			if ($public) {
				$public = true;
			}
		} catch (Exception $e) { };

		$sql = '
			SELECT document.uuid
			FROM document_incoming_invoice, document
			WHERE
			document_incoming_invoice.document_id = document.id
			AND 1';


		if (!$public) {
			try {
				$tag_ids = Setting::get_by_name('api_document_tag_ids')->value;
			} catch (Exception $e) {
				$tag_ids = '0';
			}
			if (trim($tag_ids) == '') {
				$tag_ids = '0';
			}

			$sql .= '
			AND document_id IN (SELECT document_id FROM document_tag WHERE tag_id IN ( ' . $tag_ids . '))';
		}

		$ids = $db->get_column($sql, []);
		return $ids;
	}

	/**
	 * Get expiring incoming invoices
	 *
	 * @access public
	 * @param  string $days_to_expire
	 * @return array Purchases
	 */
	public static function get_expiring($days_to_expire = '+7 days') {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_incoming_invoice WHERE paid = 0 AND DATE(expiration_date) <= ?', [ date('YmdHis', strtotime($days_to_expire)) ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get by Supplier supplier_identifier
	 *
	 * @access public
	 * @param Supplier $supplier
	 * @param string $supplier_identifier
	 */
	public static function get_by_supplier_supplier_identifier(Supplier $supplier, $supplier_identifier) {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_incoming_invoice WHERE supplier_id=? AND supplier_identifier=?', [ $supplier->id, $supplier_identifier]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get for Bank_Account_Statement_Transaction
	 *
	 * @access public
	 * @param Supplier $supplier
	 * @param string $payment_message
	 */
	public static function get_for_bank_account_statement_transaction(Bank_Account_Statement_Transaction $transaction) {
		$db = Database::get();
		$suppliers = Supplier::get_by_iban($transaction->other_account_number);

		if ($transaction->structured_message != '') {
			$structured_message = $transaction->structured_message;
			$structured_message = substr($structured_message, 0, 3) . '/' . substr($structured_message, 3, 4) . '/' . substr($structured_message, 7, 5);
			foreach ($suppliers as $supplier) {
				$id = $db->get_one('SELECT document_id FROM document_incoming_invoice WHERE payment_structured_message=? AND supplier_id=? AND balanced=0', [ $structured_message, $supplier->id ]);
				if ($id !== null) {
					return self::get_by_id($id);
				}
			}
		} else {
			foreach ($suppliers as $supplier) {
				$id = $db->get_one('SELECT document_id FROM document_incoming_invoice WHERE payment_message=? AND supplier_id=? AND balanced=0', [ $transaction->message , $supplier->id ]);
				if ($id !== null) {
					return self::get_by_id($id);
				}
			}
		}
		throw new Exception('No incoming invoice found');
	}

	/**
	 * Get by accounting_identifier
	 *
	 * @access public
	 * @param string $accounting_identifier
	 */
	public static function get_by_accounting_identifier($accounting_identifier) {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_incoming_invoice WHERE accounting_identifier=?', [ $accounting_identifier]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get match by bank_account_statement_transaction
	 *
	 * @access public
	 * @param Bank_Account_Statement_Transaction $transaction
	 * @return array $documents
	 */
	public static function get_match_by_bank_account_statement_transaction(Bank_Account_Statement_Transaction $transaction) {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_incoming_invoice WHERE price_incl=? AND balanced=0', [ $transaction->amount*-1 ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}
		return $items;
	}
}
