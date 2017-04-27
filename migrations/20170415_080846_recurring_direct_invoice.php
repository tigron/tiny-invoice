<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20170415_080846_Recurring_direct_invoice extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `invoice_queue_recurring_group`
			ADD `direct_invoice` tinyint NOT NULL AFTER `customer_contact_id`,
			ADD `direct_invoice_expiration_period` varchar(32) NOT NULL AFTER `direct_invoice`,
			ADD `direct_invoice_send_invoice` tinyint NOT NULL AFTER `direct_invoice_expiration_period`;
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
