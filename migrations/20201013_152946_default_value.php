<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20201013_152946_Default_value extends \Skeleton\Database\Migration {

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
		$db->query("UPDATE bank_account_statement_transaction SET message = NULL WHERE message = '';", []);
		$db->query("UPDATE bank_account_statement_transaction SET structured_message = NULL WHERE structured_message = '';", []);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
