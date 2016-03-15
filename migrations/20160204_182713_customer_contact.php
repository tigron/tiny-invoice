<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20160204_182713_Customer_contact extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query('ALTER TABLE `invoice_contact` RENAME TO `customer_contact`;');
		$db->query('ALTER TABLE `invoice` CHANGE `invoice_contact_id` `customer_contact_id` int(11) NOT NULL AFTER `customer_id`;');
		$db->query('ALTER TABLE `invoice_queue` CHANGE `invoice_contact_id` `customer_contact_id` int(11) NOT NULL AFTER `customer_id`;');
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
