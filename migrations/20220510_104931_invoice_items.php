<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20220510_104931_Invoice_items extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM invoice_item
								WHERE invoice_id NOT IN (SELECT id FROM invoice)
							');

		foreach ($ids as $id) {
			$invoice_item = Invoice_Item::get_by_id($id);
			$invoice_item->delete();
		}

		$db->query('
			ALTER TABLE `invoice_item`
			ADD FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`)
		');
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
