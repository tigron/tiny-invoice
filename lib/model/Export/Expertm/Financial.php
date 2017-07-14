<?php
/**
 * Export_Expertm_User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Export_Expertm_Financial extends Export_Expertm {

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		$bank_account_statement_ids = $this->get_data();

		$db = Database::get();
		$query = 'SELECT id FROM bank_account_statement WHERE id IN (0';
		foreach ($bank_account_statement_ids as $bank_account_statement_id) {
			$query .= ', ' . $db->quote($bank_account_statement_id) . ' ';
		}
		$query .= ') ORDER BY original_situation_date ASC';
		$ids = $db->get_column($query);


		$output1 = '';
		$output2 = '';





		foreach ($ids as $id) {
			$bank_account_statement = Bank_Account_Statement::get_by_id($id);

			$output1 .= $this->num(9, $bank_account_statement->bank_account->bookkeeping_account->number);
			$output1 .= $this->num(9, $bank_account_statement->sequence);
			$output1 .= $this->num(8, date('dmY', strtotime($bank_account_statement->original_situation_date)));
			$output1 .= $this->cur(20, $bank_account_statement->original_situation_balance);
			$output1 .= $this->cur(20, $bank_account_statement->new_situation_balance);
			$output1 .= $this->num(2, $this->boekhoudperiode( $bank_account_statement->original_situation_date ));
			$output1 .= $this->num(6, $this->btwmaand( $bank_account_statement->original_situation_date ));
			$output1 .= $this->num(1, 0);

			$volgnummer = 1;
			foreach ($bank_account_statement->get_bank_account_statement_transactions() as $bank_account_statement_transaction) {
				$balances = $bank_account_statement_transaction->get_bank_account_statement_transaction_balances();
				foreach ($balances as $balance) {
					if (in_array($balance->linked_object_classname, [ 'Invoice', 'Creditnote', 'Customer_Contact' ])) {
						$ventilatiecode = 1;
					} elseif (in_array($balance->linked_object_classname, [ 'Document_Incoming_Invoice', 'Document_Incoming_Creditnote', 'Supplier' ])) {
						$ventilatiecode = 2;
					} elseif (in_array($balance->linked_object_classname, [ 'Bookkeeping_Account' ])) {
						$ventilatiecode = 3;
					}

					$output2 .= $this->num(9, $volgnummer);
					$output2 .= $this->num(1, $ventilatiecode);
					$output2 .= $this->num(9, $bank_account_statement->sequence);

					if (in_array($balance->linked_object_classname, [ 'Invoice', 'Creditnote'])) {
						$tegenrekening = $balance->get_linked_object()->customer_contact->customer_contact_export_id;
					} elseif (in_array($balance->linked_object_classname, [ 'Customer_Contact' ])) {
						$tegenrekening = $balance->get_linked_object()->customer_contact_export_id;
					} elseif (in_array($balance->linked_object_classname, [ 'Document_Incoming_Invoice', 'Document_Incoming_Creditnote' ])) {
						$tegenrekening = $balance->get_linked_object()->supplier->accounting_identifier;
					} elseif (in_array($balance->linked_object_classname, [ 'Supplier' ])) {
						$tegenrekening = $balance->get_linked_object()->accounting_identifier;
					} elseif (in_array($balance->linked_object_classname, [ 'Bookkeeping_Account' ])) {
						$tegenrekening = $balance->get_linked_object()->number;
					}
					$output2 .= $this->num(9, $tegenrekening);
					$output2 .= $this->cur(20, abs($balance->amount));
					$output2 .= $this->cur(20, abs($balance->amount));
					$output2 .= $this->cur(20, 0);
					$output2 .= $this->alf(3, 'EUR');
					$output2 .= $this->cur(12, 0);
					$output2 .= $this->num(1, 0);
					$output2 .= $this->num(1, 0);
					$output2 .= $this->alf(50, $bank_account_statement_transaction->get_message());

					if ($balance->amount < 0) {
						$output2 .= $this->alf(1, 'D');
					} else {
						$output2 .= $this->alf(1, 'C');
					}

					$output2 .= $this->num(1, 5);
					$output2 .= $this->num(2, 0);
					if (in_array($balance->linked_object_classname, [ 'Invoice', 'Supplier' ])) {
						$output2 .= $this->alf(1, 'F');
					} elseif (in_array($balance->linked_object_classname, [ 'Creditnote' ])) {
						$output2 .= $this->alf(1, 'C');
					} elseif (in_array($balance->linked_object_classname, [ 'Document_Incoming_Invoice', 'Customer_contact' ])) {
						$output2 .= $this->alf(1, 'F');
					} elseif (in_array($balance->linked_object_classname, [ 'Document_Incoming_Creditnote' ])) {
						$output2 .= $this->alf(1, 'C');
					} else {
						$output2 .= $this->alf(1, '');
					}


					if (in_array($balance->linked_object_classname, [ 'Invoice' ])) {
						$output2 .= $this->num(9, $balance->linked_object_id);
					} elseif (in_array($balance->linked_object_classname, [ 'Creditnote' ])) {
						$output2 .= $this->num(9, $balance->linked_object_id);
					} elseif (in_array($balance->linked_object_classname, [ 'Document_Incoming_Invoice' ])) {
						$output2 .= $this->num(9, $balance->get_linked_object()->accounting_identifier);
					} elseif (in_array($balance->linked_object_classname, [ 'Document_Incoming_Creditnote' ])) {
						$output2 .= $this->num(9, $balance->get_linked_object()->accounting_identifier);
					} else {
						$output2 .= $this->num(9, '');
					}

					$output2 .= $this->cur(20, abs($balance->amount));
					$output2 .= $this->num(8, date('dmY', strtotime($balance->bank_account_statement_transaction->valuta_date)));
					$output2 .= $this->num(9, 0);
					$output2 .= $this->num(9, 0);
					$output2 .= "\r\n";
				}

				$volgnummer++;
			}
			$output1 .= "\r\n";
		}

		$file = \Skeleton\File\File::store('expertm_financial_' . date('Ymd') . '_1.txt', $output1);
 		$this->file_id = $file->id;
		$this->save();

		$file = \Skeleton\File\File::store('expertm_financial_' . date('Ymd') . '_2.txt', $output2);
		$export = new self();
		$export->file_id = $file->id;
		$export->save();
	}
}
