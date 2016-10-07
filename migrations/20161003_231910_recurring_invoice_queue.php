<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161003_231910_Recurring_invoice_queue extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("
			CREATE TABLE `invoice_queue_recurring` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `invoice_queue_recurring_group_id` int(11) NOT NULL,
			  `article_id` int(11) NOT NULL,
			  `description` text COLLATE utf8_unicode_ci NOT NULL,
			  `qty` decimal(10,2) NOT NULL,
			  `price` decimal(10,2) NOT NULL,
			  `vat` decimal(10,2) NOT NULL,
			  `created` datetime NOT NULL,
			  `archived` datetime NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		", []);


		$db->query("
			CREATE TABLE `invoice_queue_recurring_group` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			  `repeat_every` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
			  `next_run` datetime NOT NULL,
			  `customer_id` int(11) NOT NULL,
			  `customer_contact_id` int(11) NOT NULL,
			  `created` datetime NOT NULL,
			  `archived` datetime NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		", []);

		$db->query("
			CREATE TABLE `invoice_queue_recurring_history` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `invoice_queue_recurring_id` int(11) NOT NULL,
			  `invoice_queue_id` int(11) NOT NULL,
			  `created` datetime NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		", []);

		$db->query("
			CREATE TABLE `product_type` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `name` varchar(128) NOT NULL,
			  `identifier` varchar(32) NOT NULL
			);
		", []);

		$db->query("
			ALTER TABLE `invoice_queue_recurring`
			CHANGE `article_id` `product_type_id` int NOT NULL AFTER `invoice_queue_recurring_group_id`;
		", []);

		$db->query("
			ALTER TABLE `invoice_item`
			ADD `product_type_id` int NULL AFTER `invoice_queue_id`;
		", []);

		$db->query("
			ALTER TABLE `invoice_queue`
			ADD `product_type_id` int NOT NULL AFTER `customer_contact_id`;
		", []);

		$db->query("
			ALTER TABLE `invoice_queue_recurring_group`
			ADD `stop_after` datetime NOT NULL AFTER `next_run`;
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
