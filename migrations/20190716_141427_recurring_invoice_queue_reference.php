<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20190716_141427_Recurring_invoice_queue_reference extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `invoice_queue_recurring_group`
			ADD `direct_invoice_reference` varchar(64) NOT NULL AFTER `direct_invoice_send_invoice`;
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
