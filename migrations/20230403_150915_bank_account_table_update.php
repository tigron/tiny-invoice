<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20230403_150915_Bank_account_table_update extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("ALTER TABLE `bank_account`
                CHANGE `alias` `alias` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '' AFTER `description`,
				CHANGE `default_for_payment` `default_for_payment` tinyint(4) NOT NULL DEFAULT '0' AFTER `from_coda`;");
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
