<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20181115_133615_Uuid extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("
			ALTER TABLE `document`
			ADD `uuid` varchar(64) NOT NULL AFTER `id`;
		", []);

		$ids = $db->get_column('SELECT id FROM document', []);
		foreach ($ids as $id) {
			$document = Document::get_by_id($id);
			$document->save(false);
		}

		$db->query("
			ALTER TABLE `customer`
			ADD `uuid` varchar(64) NOT NULL AFTER `id`;
		", []);

		$ids = $db->get_column('SELECT id FROM customer', []);
		foreach ($ids as $id) {
			$customer = Customer::get_by_id($id);
			$customer->save(false);
		}

		$db->query("
			ALTER TABLE `supplier`
			ADD `uuid` varchar(64) NOT NULL AFTER `id`;
		", []);

		$ids = $db->get_column('SELECT id FROM supplier', []);
		foreach ($ids as $id) {
			$supplier = Supplier::get_by_id($id);
			$supplier->save(false);
		}

		$db->query("
			ALTER TABLE `customer_contact`
			ADD `uuid` varchar(64) NOT NULL AFTER `id`;
		", []);

		$ids = $db->get_column('SELECT id FROM customer_contact', []);
		foreach ($ids as $id) {
			$customer_contact = Customer_Contact::get_by_id($id);
			$customer_contact->save(false);
		}


	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
