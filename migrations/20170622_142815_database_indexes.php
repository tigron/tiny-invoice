<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20170622_142815_Database_indexes extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query('
			ALTER TABLE `bank_account_statement`
			ADD INDEX `bank_account_id` (`bank_account_id`),
			ADD INDEX `sequence` (`sequence`),
			ADD INDEX `bank_account_id_sequence` (`bank_account_id`, `sequence`);
		', []);

		$db->query('
			ALTER TABLE `bank_account_statement_transaction`
			ADD INDEX `bank_account_statement_id_sequence` (`bank_account_statement_id`, `sequence`);
		', []);

		$db->query('
			ALTER TABLE `bank_account_statement_transaction_balance`
			ADD INDEX `linked_object_classname_linked_object_id` (`linked_object_classname`, `linked_object_id`),
			ADD INDEX `bank_account_statement_transaction_id` (`bank_account_statement_transaction_id`);
		', []);

		$db->query('
			ALTER TABLE `bookkeeping_account`
			ADD INDEX `number` (`number`);
		', []);

		$db->query('
			ALTER TABLE `supplier`
			ADD INDEX `accounting_identifier` (`accounting_identifier`);
		', []);

		$db->query('
			ALTER TABLE `document_incoming_creditnote`
			ADD INDEX `accounting_identifier` (`accounting_identifier`);
		', []);

		$db->query('
			ALTER TABLE `document_incoming_invoice`
			ADD INDEX `accounting_identifier` (`accounting_identifier`);
		', []);

		$db->query('
			ALTER TABLE `bank_account_statement_transaction`
			ADD INDEX `date` (`date`);
		', []);

		$db->query('
			ALTER TABLE `document_incoming_invoice`
			ADD INDEX `document_id` (`document_id`);
		', []);

		$db->query('
			ALTER TABLE `document`
			ADD INDEX `classname` (`classname`);
		', []);


	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
