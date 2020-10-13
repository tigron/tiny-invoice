<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20200925_131121_Invoice_paid extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query('UPDATE invoice SET paid=0 WHERE paid is null', []);
		$db->query("
			ALTER TABLE `invoice`
			CHANGE `paid` `paid` tinyint(4) NOT NULL DEFAULT '0' AFTER `customer_contact_id`;
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
