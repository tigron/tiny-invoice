<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20191011_114008_Invoice_internal_reference extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `invoice`
			ADD `internal_reference` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `reference`;
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
