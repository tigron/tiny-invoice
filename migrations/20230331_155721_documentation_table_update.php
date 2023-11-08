<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20230331_155721_Documentation_table_update extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("ALTER TABLE `document_documentation` ADD `supplier_identifier` varchar(128) COLLATE 'latin1_swedish_ci' NULL;");
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
