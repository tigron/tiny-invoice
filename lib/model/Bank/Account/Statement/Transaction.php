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
	 * Array with link information
	 *
	 * @access private
	 * @var array $link_information
	 */
	private $link_information = [];

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
		$sum = bcadd($sum, 0, 2);

		foreach ($balances as $balance) {
			$sum = bcsub($sum, $balance->amount, 2);
		}
		$sum = bcadd($sum, 0, 2);
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
	 * Add Link
	 *
	 * @access public
	 * @param Object $object
	 * @param double $amount
	 */
	public function add_link($object, $amount) {
		$this->link_information[] = [
			'link_to' => $object,
			'amount' => $amount
		];
	}

	/**
	 * Check links
	 *
	 * @access public
	 * @return boolean $valid
	 */
	public function check_links() {
		$total_amount = 0;

		foreach ($this->link_information as $link_information) {
			$total_amount += $link_information['amount'];
		}

		if (bccomp($total_amount, $this->get_balance(), 3) == 0) {
			return true;
		}
		return false;
	}

	/**
	 * Reset links
	 *
	 * @access public
	 */
	public function reset_links() {
		$this->link_information = [];
	}

	/**
	 * Get links
	 *
	 * @access public
	 */
	public function get_links() {
		return $this->link_information;
	}

	/**
	 * Apply links
	 *
	 * @access public
	 */
	public function apply_links() {
		foreach ($this->link_information as $link_information) {
			$classname = strtolower(get_class($link_information['link_to']));
			if ($classname == 'invoice') {
				$this->link_invoice($link_information['link_to'], $link_information['amount']);
			} elseif ($classname == 'supplier') {
				$this->link_supplier($link_information['link_to'], $link_information['amount']);
			} elseif ($classname == 'customer_contact') {
				$this->link_customer_contact($link_information['link_to'], $link_information['amount']);
			} elseif ($classname == 'document_incoming_invoice') {
				$this->link_document($link_information['link_to'], $link_information['amount']);
			} elseif ($classname == 'bookkeeping_account') {
				$this->link_bookkeeping_account($link_information['link_to'], $link_information['amount']);
			}
		}
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
			$amount = $this->get_balance();
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
		$transfer->bank_account_statement_transaction_balance_id = $balance->id;
		$transfer->save();

		$invoice->add_transfer($transfer);
	}

	/**
	 * Link to supplier
	 *
	 * @access public
	 * @param Supplier $supplier
	 */
	public function link_supplier(Supplier $supplier, $amount = null) {
		if ($amount === null) {
			$amount = $this->get_balance();
		}

		$balance = new Bank_Account_Statement_Transaction_Balance();
		$balance->bank_account_statement_transaction_id = $this->id;
		$balance->set_linked_object($supplier);
		$balance->amount = $amount;
		$balance->save();

		if ($this->get_balance() == 0) {
			$this->balanced = true;
			$this->save(false);
		}
	}

	/**
	 * Link to customer_contact
	 *
	 * @access public
	 * @param Supplier $supplier
	 */
	public function link_customer_contact(Customer_Contact $customer_customer_contact, $amount = null) {
		if ($amount === null) {
			$amount = $this->get_balance();
		}

		$balance = new Bank_Account_Statement_Transaction_Balance();
		$balance->bank_account_statement_transaction_id = $this->id;
		$balance->set_linked_object($customer_customer_contact);
		$balance->amount = $amount;
		$balance->save();

		if ($this->get_balance() == 0) {
			$this->balanced = true;
			$this->save(false);
		}
	}

	/**
	 * Link document
	 *
	 * @access public
	 * @param Document $document
	 * @param decimal $amount
	 */
	public function link_document(Document $document, $amount = null) {
		if ($amount === null) {
			$amount = $this->get_balance();
		}

		$balance = new Bank_Account_Statement_Transaction_Balance();
		$balance->bank_account_statement_transaction_id = $this->id;
		$balance->set_linked_object($document);
		$balance->amount = $amount;
		$balance->save();

		if ($this->get_balance() == 0) {
			$this->balanced = true;
			$this->save();
		}

		if ($document->get_balance() == 0) {
			$document->balanced = true;
			$document->save(false);
		}
	}

	/**
	 * Link bookkeeping_account
	 *
	 * @access public
	 * @param Bookkeeping_Account $bookkeeping_account
	 */
	public function link_bookkeeping_account(Bookkeeping_Account $bookkeeping_account, $amount = null) {
		if ($amount === null) {
			$amount = $this->get_balance();
		}

		$balance = new Bank_Account_Statement_Transaction_Balance();
		$balance->bank_account_statement_transaction_id = $this->id;
		$balance->set_linked_object($bookkeeping_account);
		$balance->amount = $amount;
		$balance->save();

		if ($this->get_balance() == 0) {
			$this->balanced = true;
			$this->save();
		}
	}

	/**
	 * Automatic link
	 *
	 * @access public
	 */
	public function automatic_link() {
		$linked = false;

		if ($this->amount > 0) {
			try {
				$this->automatic_link_invoice();
				$linked = true;
			} catch (Exception $e) { }

		}
		if ($this->amount < 0) {
			try {
				$this->automatic_link_incoming_invoice();
				$linked = true;
			} catch (Exception $e) {	}
		}

		if (!$linked) {
			$this->automatic_link_extractor();
		}
	}

	/**
	 * Get possible matches
	 *
	 * @access public
	 * @return array $matches
	 */
	public function get_possible_matches() {
		$matches = [];

		if ($this->amount < 0) {
			$matches['document_incoming_invoice'] = Document_Incoming_Invoice::get_match_by_bank_account_statement_transaction($this);
			if ($this->other_account_number != '') {
				$matches['supplier'] = Supplier::get_by_iban($this->other_account_number);
			}
		}

		$extractors = Extractor_Bank_Account_Statement_Transaction::get_all();
		foreach ($extractors as $extractor) {
			if (!$extractor->match($this)) {
				continue;
			}
			try {
				$data = $extractor->extract_data($this);
				if (!$this->check_links()) {
					$this->reset_links();
				}

				foreach ($this->link_information as $link) {
					$classname = strtolower(get_class($link['link_to']));
					if (!isset($matches[ $classname ])) {
						$matches[$classname] = [];
					}
					$matches[$classname][] = $link['link_to'];
				}

			} catch (Extractor_Eval_Exception $e) {
				continue;
			}
		}

		return $matches;
	}

	/**
	 * Try a link with an extractor
	 *
	 * @access private
	 */
	private function automatic_link_extractor() {
		$extractors = Extractor_Bank_Account_Statement_Transaction::get_all();

		foreach ($extractors as $extractor) {
			if (!$extractor->match($this)) {
				continue;
			}

			try {
				$data = $extractor->extract_data($this);
				if (!$this->check_links()) {
					$this->reset_links();
					continue;
				}

				$this->apply_links();
				return true;
			} catch (Extractor_Eval_Exception $e) {
				continue;
			}
		}

		throw new Exception('Automatic link with extractors not possible');
	}

	/**
	 * Automatic link with invoice
	 *
	 * @access private
	 */
	private function automatic_link_invoice() {
		preg_match("/\+\+\+(\d{3}\/\d{4}\/\d{5})\+\+\+/", $this->get_message(), $output_array);
		if (isset($output_array[1])) {
			try {
				$invoice = Invoice::get_by_ogm($output_array[0]);
			} catch (Exception $e) {
				throw new Exception('No invoice found for this message');
			}
			if (bccomp($invoice->get_balance(), $this->amount, 3) == 0) {
				$this->link_invoice($invoice);
				return;
			}
		}
		throw new Exception('No invoice found');
	}

	/**
	 * Automic link with incoming invoice
	 *
	 * @access private
	 */
	private function automatic_link_incoming_invoice() {
		$incoming_invoice = Document_Incoming_Invoice::get_for_bank_account_statement_transaction($this);
		$this->link_document($incoming_invoice);
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
	 * get_oldest_unbalanced
	 *
	 * @access public
	 * @return Bank_Account_Statement $bank_account_statement
	 */
	public static function get_oldest_unbalanced($start = null) {
		$db = Database::get();
		if ($start === null) {
			$id = $db->get_one('SELECT id FROM bank_account_statement_transaction WHERE balanced=0 ORDER BY date ASC LIMIT 1', [ ]);
		} else {
			$id = $db->get_one('SELECT id FROM bank_account_statement_transaction WHERE balanced=0 AND id>? ORDER BY date ASC LIMIT 1', [ $start ]);
		}
		if ($id === null) {
			throw new Exception('No transactions found');
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
