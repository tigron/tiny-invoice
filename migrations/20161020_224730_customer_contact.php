<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161020_224730_Customer_contact extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `customer_contact`
			CHANGE `invoice_method_id` `invoice_method_id` int(11) NOT NULL DEFAULT '1' AFTER `customer_id`;
		", []);

		$db->query("
			UPDATE customer_contact SET invoice_method_id=1;
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
