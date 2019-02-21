<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @author Lionel Laffineur <lionel@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20171221_221542_Transaction_link_outgoing_invoice extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$extractor_bank_account_statement_transaction = new Extractor_Bank_Account_Statement_Transaction();
		$extractor_bank_account_statement_transaction->bank_account_statement_transaction_id = 0;
		$extractor_bank_account_statement_transaction->name = 'Outgoing invoices with correct OGM';
		$extractor_bank_account_statement_transaction->eval = '
if ($transaction->amount < 0) {
	return false;
}

preg_match("/\+\+\+(\d{3}\/\d{4}\/\d{5})\+\+\+/", $transaction->get_message(), $output_array);
if (isset($output_array[1])) {
	try {
		$invoice = Invoice::get_by_ogm($output_array[0]);
	} catch (Exception $e) {
		return false;
	}
	if (bccomp($invoice->get_balance(), $transaction->amount, 3) == 0) {
		$transaction->link_invoice($invoice);
		return true;
	}
}
return false;
		';
		$extractor_bank_account_statement_transaction->fingerprint_message = '';
		$extractor_bank_account_statement_transaction->fingerprint_other_account_name = '';
		$extractor_bank_account_statement_transaction->fingerprint_other_account_number = '';
		$extractor_bank_account_statement_transaction->save();
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
