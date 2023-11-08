<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20230331_145943_Change_uk_to_non_european extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("UPDATE `country` SET
					`id` = '185',
					`ison` = '826',
					`iso2` = 'GB',
					`iso3` = 'GBR',
					`vat` = 'GB',
					`european` = '0',
					`european_continent` = '1'
					WHERE `id` = '185';");
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
