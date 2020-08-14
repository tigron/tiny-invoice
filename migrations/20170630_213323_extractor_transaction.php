<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20170630_213323_Extractor_transaction extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query('
			ALTER TABLE `extractor`
			RENAME TO `extractor_pdf`;
		', []);

		$db->query("
			ALTER TABLE `extractor_fingerprint`
			RENAME TO `extractor_pdf_fingerprint`;
		", []);

		$db->query("
			ALTER TABLE `extractor_pdf_fingerprint`
			CHANGE `extractor_id` `extractor_pdf_id` int(11) NOT NULL AFTER `id`,
			ADD FOREIGN KEY (`extractor_pdf_id`) REFERENCES `extractor_pdf` (`id`);
		", []);

		$db->query("
			CREATE TABLE `extractor_bank_account_statement_transaction` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `bank_account_statement_transaction_id` int(11) NULL,
			  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			  `fingerprint_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `fingerprint_other_account_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			  `fingerprint_other_account_number` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			  `eval` text COLLATE utf8_unicode_ci NOT NULL,
			  `created` datetime NOT NULL,
			  `updated` datetime NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		", []);

		$bank_account_statements = Bank_Account_Statement::get_all();
		foreach ($bank_account_statements as $bank_account_statement) {
			$sequence = $bank_account_statement->sequence;
			$year = substr($sequence, 0, 4);
			$number = substr($sequence, 4);
			$sequence = $year . str_pad($number, 5, "0", STR_PAD_LEFT);
			$bank_account_statement->sequence = $sequence;
			$bank_account_statement->save();
		}

		$db->query("
			ALTER TABLE `bank_account_statement`
			CHANGE `date` `original_situation_date` date NOT NULL AFTER `sequence`,
			CHANGE `original_balance` `original_situation_balance` decimal(10,3) NOT NULL AFTER `original_situation_date`,
			ADD `new_situation_date` date NOT NULL AFTER `original_situation_balance`,
			CHANGE `new_balance` `new_situation_balance` decimal(10,3) NOT NULL AFTER `new_situation_date`;
		", []);

		$db->query("
			UPDATE bank_account_statement SET new_situation_date=original_situation_date
		", []);

		$bank_accounts = Bank_Account::get_all();
		foreach ($bank_accounts as $bank_account) {
			$previous = null;
			foreach ($bank_account->get_bank_account_statements() as $bank_account_statement) {
				if ($previous !== null) {
					$bank_account_statement->original_situation_date = $previous->new_situation_date;
					$bank_account_statement->save();
				}

				$previous = $bank_account_statement;
			}
		}
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
