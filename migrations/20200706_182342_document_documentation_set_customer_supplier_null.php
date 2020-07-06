<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20200706_182342_Document_documentation_set_customer_supplier_null extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("
			ALTER TABLE `document_documentation`
			CHANGE `customer_id` `customer_id` int(11) NULL AFTER `document_id`,
			CHANGE `supplier_id` `supplier_id` int(11) NULL AFTER `customer_id`;
		");
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
