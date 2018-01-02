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
		$parser = new Parser();
		$statements = $parser->parseFile($file->get_path(), 'raw');

		foreach ($statements as $statement) {
			try {
				$bank_account = Bank_Account::get_by_number($statement->original_situation->account_number);
			} catch (Exception $e) {
				$bank_account = new Bank_Account();
			}
			$bank_account->number = $statement->original_situation->account_number;
			$bank_account->description = $statement->original_situation->account_description;
			$bank_account->name = $statement->original_situation->account_name;
			$bank_account->save();

			$statement_sequence_number = $statement->original_situation->statement_sequence_number;
			$year = date('Y', strtotime($statement->original_situation->date));
			$month = date('m', strtotime($statement->original_situation->date));
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
			$bank_account_statement->original_situation_date = $statement->original_situation->date;
			$bank_account_statement->original_situation_balance = $statement->original_situation->balance;
			$bank_account_statement->new_situation_date = $statement->new_situation->date;
			$bank_account_statement->new_situation_balance = $statement->new_situation->balance;
			$bank_account_statement->save();


			foreach ($statement->transactions as $transaction) {
				try {
					$bank_account_statement_transaction = Bank_Account_Statement_Transaction::get_by_bank_account_statement_sequence($bank_account_statement, $transaction->line21->sequence_number);
				} catch (Exception $e) {
					$bank_account_statement_transaction = new Bank_Account_Statement_Transaction();
					$bank_account_statement_transaction->bank_account_statement_id = $bank_account_statement->id;
					$bank_account_statement_transaction->sequence = $transaction->line21->sequence_number;
				}
				$bank_account_statement_transaction->date = $transaction->line21->transaction_date;
				$bank_account_statement_transaction->valuta_date = $transaction->line21->valuta_date;
				$bank_account_statement_transaction->amount = $transaction->line21->amount;
				if ($transaction->line21->has_structured_message) {
					if ($transaction->line21->structured_message_type == 114) {
						$bank_account_statement_transaction->message = $transaction->line21->structured_message_full;
						if (isset($transaction->line22->message)) {
							$bank_account_statement_transaction->message = $bank_account_statement_transaction->message . $transaction->line22->message;
						}
					} else {
						$bank_account_statement_transaction->structured_message = $transaction->line21->structured_message . '';
					}
				} else {
					$bank_account_statement_transaction->message = $transaction->line21->message;
					if (isset($transaction->line22->message)) {
						$bank_account_statement_transaction->message = $bank_account_statement_transaction->message . $transaction->line22->message;
					}
				}

				if (isset($transaction->line22->other_account_bic)) {
					$bank_account_statement_transaction->other_account_bic = $transaction->line22->other_account_bic;
				}
				if (isset($transaction->line23->other_account_number_and_currency)) {
					$bank_account_statement_transaction->other_account_number = $transaction->line23->other_account_number_and_currency;
				}
				if (isset($transaction->line23->other_account_name)) {
					$bank_account_statement_transaction->other_account_name = $transaction->line23->other_account_name;
				}
				$bank_account_statement_transaction->save();
			}

		}
	}

}
