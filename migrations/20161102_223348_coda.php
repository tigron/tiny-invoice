<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161102_223348_Coda extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `bank_account`
			DROP `identifier`,
			ADD `description` varchar(128) COLLATE 'latin1_swedish_ci' NOT NULL,
			ADD `number` varchar(128) COLLATE 'latin1_swedish_ci' NOT NULL AFTER `description`;
		", []);

		$db->query("
			INSERT INTO `bank_account_statement_parser` (`id`, `classname`) VALUES
			(1,	'Bank_Account_Statement_Parser_Belfius_Csv'),
			(2,	'Bank_Account_Statement_Parser_Coda');
		", []);

		$db->query("
			ALTER TABLE `bank_account_statement`
			ADD `sequence` varchar(12) NOT NULL AFTER `bank_account_id`;
		", []);

		$db->query("
			ALTER TABLE `bank_account_statement_transaction`
			ADD `sequence` int(11) NOT NULL AFTER `bank_account_statement`;
		", []);

		$db->query("
			ALTER TABLE `bank_account_statement`
			CHANGE `sequence` `sequence` int NOT NULL AFTER `bank_account_id`;
		", []);

		$db->query("
			ALTER TABLE `bank_account_statement_transaction`
			CHANGE `bank_account_statement` `bank_account_statement_id` int(11) NOT NULL AFTER `id`,
			ADD FOREIGN KEY (`bank_account_statement_id`) REFERENCES `bank_account_statement` (`id`);
		", []);

		$db->query("
			ALTER TABLE `bank_account_statement_transaction`
			DROP FOREIGN KEY `bank_account_statement_transaction_ibfk_1`
		", []);

		$db->query("
			ALTER TABLE `bank_account_statement_transaction`
			CHANGE `message` `message` varchar(255) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `amount`,
			CHANGE `structured_message` `structured_message` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `message`,
			ADD `other_account_number` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL,
			ADD `other_account_name` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `other_account_number`,
			ADD `other_account_bic` varchar(128) COLLATE 'utf32_unicode_ci' NOT NULL AFTER `other_account_name`;
		", []);

		$db->query("
			CREATE TABLE `bank_account_statement_transaction_balance` (
			  `id` int NOT NULL,
			  `bank_account_statement_transaction_id` int NOT NULL,
			  `linked_object_classname` varchar(64) NOT NULL,
			  `linked_object_id` int NOT NULL,
			  `amount` decimal(10,2) NOT NULL
			) ENGINE='InnoDB';
		", []);


	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
