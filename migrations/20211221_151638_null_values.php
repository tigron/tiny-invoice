<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20211221_151638_Null_values extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("ALTER TABLE `extractor_pdf` CHANGE `updated` `updated` datetime NULL AFTER `created`;", []);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
