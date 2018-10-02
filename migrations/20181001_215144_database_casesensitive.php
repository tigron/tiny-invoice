<?php
/**
 * Database migration class
 *
 */

use \Skeleton\Database\Database;

class Migration_20181001_215144_Database_casesensitive extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `country`
			CHANGE `ISON` `ison` varchar(4) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `name`,
			CHANGE `ISO2` `iso2` varchar(2) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `ison`,
			CHANGE `ISO3` `iso3` varchar(3) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `iso`;
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
