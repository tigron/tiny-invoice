<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20211216_101324_Invoice_number_unique extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `invoice` ADD UNIQUE `number` (`number`);
		", []);
		$db->query("
			ALTER TABLE `creditnote` ADD UNIQUE `number` (`number`);
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
