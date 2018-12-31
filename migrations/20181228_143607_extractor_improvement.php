<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20181228_143607_Extractor_improvement extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `extractor_pdf`
			ADD `last_used` datetime NULL;
		", []);

		$db->query("
			ALTER TABLE `extractor_pdf`
			CHANGE `document_id` `document_id` int(11) NOT NULL AFTER `name`,
			CHANGE `eval` `eval` text COLLATE 'latin1_swedish_ci' NOT NULL AFTER `document_id`,
			CHANGE `last_used` `last_used` datetime NULL AFTER `eval`,
			CHANGE `created` `created` datetime NOT NULL AFTER `last_used`,
			ADD `updated` datetime NOT NULL;
		", []);

		$db->query("
			ALTER TABLE `extractor_bank_account_statement_transaction`
			ADD `last_used` datetime NULL AFTER `eval`;
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
