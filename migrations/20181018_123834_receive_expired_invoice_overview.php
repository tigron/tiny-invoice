<?php
/**
 * Database migration class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */


use \Skeleton\Database\Database;

class Migration_20181018_123834_Receive_expired_invoice_overview extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("ALTER TABLE `user`
					ADD `receive_expired_invoice_overview` tinyint(1) NOT NULL AFTER `admin`;");
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
