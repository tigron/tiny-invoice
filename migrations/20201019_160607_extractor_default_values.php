<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20201019_160607_Extractor_default_values extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `extractor_bank_account_statement_transaction`
			CHANGE `fingerprint_message` `fingerprint_message` varchar(255) COLLATE 'utf8_unicode_ci' NULL AFTER `name`,
			CHANGE `fingerprint_other_account_name` `fingerprint_other_account_name` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `fingerprint_message`,
			CHANGE `fingerprint_other_account_number` `fingerprint_other_account_number` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `fingerprint_other_account_name`,
			CHANGE `eval` `eval` text COLLATE 'utf8_unicode_ci' NULL AFTER `fingerprint_other_account_number`,
			CHANGE `updated` `updated` datetime NULL AFTER `created`;
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
