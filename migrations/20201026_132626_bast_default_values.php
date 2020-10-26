<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20201026_132626_Bast_default_values extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `bank_account_statement_transaction`
			CHANGE `message` `message` varchar(255) COLLATE 'utf8_unicode_ci' NULL AFTER `amount`,
			CHANGE `structured_message` `structured_message` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `message`;
		", []);
		$db->query("
			ALTER TABLE `bank_account_statement_transaction`
			CHANGE `other_account_number` `other_account_number` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `structured_message`,
			CHANGE `other_account_name` `other_account_name` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `other_account_number`,
			CHANGE `other_account_bic` `other_account_bic` varchar(128) COLLATE 'utf32_unicode_ci' NULL AFTER `other_account_name`;
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
