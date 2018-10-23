<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20181022_152812_Bank_account_alias extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `bank_account`
			ADD `alias` varchar(128) COLLATE 'latin1_swedish_ci' NOT NULL AFTER `description`;
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
