<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20210304_092817_Invoice_conditions extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("
			INSERT INTO `setting` (`name`, `value`)
			VALUES ('file_id', '');
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
