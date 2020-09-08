<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20190726_172626_Table_collation extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `bank_account_statement_transaction_balance`
			COLLATE 'utf8_unicode_ci';
		", []);

		$db->query("
			ALTER TABLE `bookkeeping_account`
			COLLATE 'utf8_unicode_ci';
		", []);

		$db->query("
			ALTER TABLE `creditnote`
			CHANGE `vat_mode` `vat_mode` enum('line','group') COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT 'group' AFTER `price_incl`,
			COLLATE 'utf8_unicode_ci';
		", []);

		$db->query("
			ALTER TABLE `creditnote_item`
			CHANGE `description` `description` text COLLATE 'utf8_unicode_ci' NOT NULL AFTER `vat_rate_id`,
			COLLATE 'utf8_unicode_ci';
		", []);

		$db->query("
			ALTER TABLE `creditnote_vat`
			COLLATE 'utf8_unicode_ci';
		", []);

		$db->query("
			ALTER TABLE `document_contract`
			COLLATE 'utf8_unicode_ci';
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
