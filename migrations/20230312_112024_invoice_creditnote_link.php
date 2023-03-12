<?php
/**
 * Database migration class
 *
 */

use \Skeleton\Database\Database;

class Migration_20230312_112024_Invoice_creditnote_link extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		/*
		$db->query("
			ALTER TABLE `creditnote`
			ADD `invoice_id` int(11) NULL AFTER `customer_id`,
			ADD FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`);
		", []);
		*/

		$ids = $db->get_column("
			SELECT id FROM `log` WHERE `content` LIKE '%Creditnote % created for invoice%'
		", []);

		foreach ($ids as $id) {
			$log = \Log::get_by_id($id);
			preg_match('/Creditnote (\d*) created for invoice/', $log->content, $output_array);

			try {
				$creditnote = \Creditnote::get_by_number($output_array[1]);
			} catch (\Exception $e) {
				continue;
			}
			$creditnote->invoice_id = $log->object_id;
			$creditnote->save();
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
