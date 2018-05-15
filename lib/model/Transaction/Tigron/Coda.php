<?php
/**
 * Transaction_Reminder_Invoice
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Transaction_Tigron_Coda extends Transaction {

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		try {
			$enable_invoice_reminder = Setting::get_by_name('enable_codabox');
		} catch (Exception $e) {
			$this->schedule('1 hour');
			return;
		}

		if (!$enable_invoice_reminder->value) {
			$this->schedule('1 hour');
			return;
		}

		try {
			$tigron_api_key = Setting::get_by_name('tigron_api_key');
		} catch (Exception $e) {
			$this->schedule('1 hour');
			return;
		}

		\Tigron\Codabox\Config::$key = $tigron_api_key->value;
		$coda_files = \Tigron\Codabox\Coda::get_unprocessed();
		foreach ($coda_files as $coda_file) {
			$coda = \Skeleton\File\File::store($coda_file->name, base64_decode($coda_file->content));
			$parser = new Bank_Account_Statement_Parser_Coda();
			$parser->parse($coda);
			$coda_file->mark_processed();
		}

		$transactions = Bank_Account_Statement_Transaction::get_unbalanced();
		foreach ($transactions as $transaction) {
			if ($transaction->id <= $latest_transaction->id) {
				continue;
			}
			try {
				$transaction->automatic_link();
			} catch (Exception $e) { }
		}

		$this->schedule('1 hour');
	}
}
