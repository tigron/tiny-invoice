<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20200925_133303_Paid_default_value extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
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
