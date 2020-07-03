<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20200511_104448_Add_archived_to_product_type extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("
			ALTER TABLE `product_type`
			ADD COLUMN `archived` DATETIME NULL AFTER `identifier`;
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
