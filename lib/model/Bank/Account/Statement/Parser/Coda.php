<?php
/**
 * Bank_Account_Statement_Transaction class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use Codelicious\Coda\Parser;

class Bank_Account_Statement_Parser_Coda extends Bank_Account_Statement_Parser {

	/**
	 * Detect
	 *
	 * @access public
	 * @param \Skeleton\File\File $file
	 * @return bool $valid
	 */
	public function detect(\Skeleton\File\File $file) {
		$parser = new Parser();
		$statements = $parser->parseFile($file->get_path(), 'simple');

		if ($file->mime_type != 'text/plain' OR count($statements) == 0) {
			return false;
		}
		return true;
	}

	/**
	 * Parse
	 *
	 * @access public
	 * @param \Skeleton\File\File $file
	 */
	public function parse(\Skeleton\File\File $file) {
		$parser = new Codelicious\Coda\Parser();
		$statements = $parser->parseFile($file->get_path(), 'raw');
print_r($statements);

		foreach ($statements as $statement) {
			$statement_account = $statement->getAccount();
			try {
				$bank_account = Bank_Account::get_by_number($statement_account->getNumber());
			} catch (Exception $e) {
				$bank_account = new Bank_Account();
			}
			$bank_account->number = trim($statement_account->getNumber());
			$bank_account->description = $statement_account->getDescription();
			$bank_account->name = $statement_account->getName();
			$bank_account->bic = $statement_account->getBic();
			$bank_account->from_coda = true;
			$bank_account->save();

			$statement_sequence_number = $statement->getSequenceNumber();
			$year = $statement->getDate()->format('Y');
			$month = $statement->getDate()->format('m');

			if ($month == 12 AND $statement_sequence_number < 20) {
				$year++;
			}
			if ($month == 1 AND $statement_sequence_number > 300) {
				$year--;
			}
			$statement_sequence_number = $year . str_pad($statement_sequence_number, 5, "0", STR_PAD_LEFT);

			try {
				$bank_account_statement = Bank_Account_Statement::get_by_bank_account_sequence($bank_account, $statement_sequence_number);
			} catch (Exception $e) {
				$bank_account_statement = new Bank_Account_Statement();
				$bank_account_statement->bank_account_id = $bank_account->id;
				$bank_account_statement->sequence = $statement_sequence_number;
			}
			$bank_account_statement->original_situation_date = $statement->getDate()->format('Y-m-d');
			$bank_account_statement->original_situation_balance = $statement->getInitialBalance();
			$bank_account_statement->new_situation_date = $statement->getNewBalance();
			$bank_account_statement->new_situation_balance = $statement->getNewDate()->format('Y-m-d');
			$bank_account_statement->save();


			foreach ($statement->getTransactions() as $transaction) {
				try {
					$bank_account_statement_transaction = Bank_Account_Statement_Transaction::get_by_bank_account_statement_sequence($bank_account_statement, $transaction->getTransactionSequence());
				} catch (Exception $e) {
					$bank_account_statement_transaction = new Bank_Account_Statement_Transaction();
					$bank_account_statement_transaction->bank_account_statement_id = $bank_account_statement->id;
					$bank_account_statement_transaction->sequence = $transaction->getTransactionSequence();
				}
				$bank_account_statement_transaction->date = $transaction->getTransactionDate()->format('Y-m-d');
				$bank_account_statement_transaction->valuta_date = $transaction->getValutaDate()->format('Y-m-d');
				$bank_account_statement_transaction->amount = $transaction->getAmount();
				
				$structured_message = $transaction->getStructuredMessage();
				if (empty($structured_message)) {
					$bank_account_statement_transaction->structured_message = $structured_message;
				} else {
					$bank_account_statement_transaction->message = $transaction->getMessage();				
				}

				$other_account = $transaction->getAccount();
				$bank_account_statement_transaction->other_account_bic = $other_account->getBic();
				$bank_account_statement_transaction->other_account_number = $other_account->getNumber();
				$bank_account_statement_transaction->other_account_name = $other_account->getName();												
				$bank_account_statement_transaction->save();
			}

		}
	}

}
