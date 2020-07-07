<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20200707_114229_Document_contract_set_customer_supplier_null extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("
			ALTER TABLE `document_contract`
			CHANGE `customer_id` `customer_id` int(11) NULL AFTER `to_date`,
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
