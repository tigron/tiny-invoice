<?php
/**
 * Bank_Account_Statement_Transaction class
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Bank_Account_Statement_Parser_Belfius_Csv extends Bank_Account_Statement_Parser {

	/**
	 * Detect
	 *
	 * @access public
	 * @param \Skeleton\File\File $file
	 * @return bool $valid
	 */
	public function detect(\Skeleton\File\File $file) {
		$content = file_get_contents($file->get_path());
		$lines = explode("\n", $content);
		$lines = array_slice($lines, 12);
		foreach ($lines as $key => $line) {
			$line = trim(str_replace("\t", ";", $line));
			if ($line == '') {
				continue;
			}
			$lines[$key] = $line;
		}

		$titles = array_shift($lines);
		$titles = explode(';', $titles);
		$transactions = [];

		foreach ($lines as $line) {
			$fields = explode(';', $line);
			if (count($fields) != count($titles)) {
				continue;
			}
			$transaction = [];
			foreach ($fields as $key => $value) {
				if (!isset($titles[$key])) {
					return false;
				}
				$transaction[ $titles[$key] ] = $value;
			}
			$transactions[] = $transaction;
		}

		foreach ($transactions as $transaction) {
			$required_keys = [ 'Rekening', 'Rekening tegenpartij', 'Valutadatum', 'Bedrag', 'Naam tegenpartij bevat', 'Straat en nummer', 'Postcode en plaats', 'Transactie', 'Afschriftnummer' ];
			foreach ($required_keys as $required_key) {
				if (!isset($transaction[$required_key])) {
					return false;
				}
			}
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
		$content = file_get_contents($file->get_path());
		$lines = explode("\n", $content);
		$lines = array_slice($lines, 12);
		foreach ($lines as $key => $line) {
			$line = trim(str_replace("\t", ";", $line));
			if ($line == '') {
				continue;
			}
			$lines[$key] = $line;
		}

		$titles = array_shift($lines);
		$titles = explode(';', $titles);
		$transactions = [];

		foreach ($lines as $line) {
			$fields = explode(';', $line);
			if (count($fields) != count($titles)) {
				continue;
			}
			$transaction = [];
			foreach ($fields as $key => $value) {
				if (!isset($titles[$key])) {
					throw new Exception('Title not found. Problem with CSV syntax');
				}
				$transaction[ $titles[$key] ] = $value;
			}
			$transactions[] = $transaction;
		}

		foreach ($transactions as $transaction) {
			$required_keys = [ 'Rekening', 'Rekening tegenpartij', 'Valutadatum', 'Transactienummer', 'Bedrag', 'Naam tegenpartij bevat', 'Straat en nummer', 'Postcode en plaats', 'Transactie', 'Afschriftnummer' ];
			foreach ($required_keys as $required_key) {
				if (!isset($transaction[$required_key])) {
					throw new Exception('Required field is missing: ' . $required_key);
				}
			}
		}

		foreach ($transactions as $transaction) {
			try {
				$bank_account = Bank_Account::get_by_number(str_replace(' ', '', $transaction['Rekening']));
			} catch (Exception $e) {
				$bank_account = new Bank_Account();
				$bank_account->number = str_replace(' ', '', $transaction['Rekening']);
				$bank_account->save();
			}

			/**
			 * Check the statement identifier
			 */
			$date = split("/", $transaction['Valutadatum']);
			$date = $date[2] . '-' . $date[1] . '-' . $date[0];
			$number = $transaction['Afschriftnummer']; //afschrift number
			try {
				$bank_account_statement = Bank_Account_Statement::get_by_bank_account_sequence($bank_account, $number);
			} catch (Exception $e) {
				$bank_account_statement = new Bank_Account_Statement();
				$bank_account_statement->bank_account_id = $bank_account->id;
				$bank_account_statement->sequence = $number;
				$bank_account_statement->date = $date;
				$bank_account_statement->save();
			}

			try {
				$bank_account_statement_transaction = Bank_Account_Statement_Transaction::get_by_bank_account_statement_sequence($bank_account_statement, $transaction['Transactienummer']);
			} catch (Exception $e) {
				$bank_account_statement_transaction = new Bank_Account_Statement_Transaction();
				$bank_account_statement_transaction->bank_account_statement_id = $bank_account_statement->id;
				$bank_account_statement_transaction->sequence_number = $transaction['Transactienummer'];
			}

			$bank_account_statement_transaction->date = $date;
			$bank_account_statement_transaction->valudate_date = $date;
			$bank_account_statement_transaction->amount = str_replace(',', '.', $transaction['Bedrag']);
			if (isset($transaction['Mededelingen'])) {
				$bank_account_statement_transaction->message = $transaction['Mededelingen'];
			} else {
				$bank_account_statement_transaction->message = $transaction['Transactie'];
			}

			$bank_account_statement_transaction->other_account_name = $transaction['Naam tegenpartij bevat'];
			$bank_account_statement_transaction->other_account_number = $transaction['Rekening tegenpartij'];
			$bank_account_statement_transaction->save();
		}
	}

}
