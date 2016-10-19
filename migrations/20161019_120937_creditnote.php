<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161019_120937_Creditnote extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE `creditnote` (
			  `id` int NOT NULL,
			  `customer_id` int NOT NULL,
			  `customer_contact_id` int NOT NULL,
			  `file_id` int NOT NULL,
			  `number` int NOT NULL,
			  `price_excl` decimal(10,2) NOT NULL,
			  `price_incl` decimal(10,2) NOT NULL,
			  `created` datetime NOT NULL
			) ENGINE='InnoDB';
		", []);

		$db->query("
			CREATE TABLE `creditnote_item` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `creditnote_id` int NOT NULL,
			  `product_type_id` int NOT NULL,
			  `description` text NOT NULL,
			  `qty` decimal(10,2) NOT NULL,
			  `price` decimal(10,2) NOT NULL,
			  `vat` decimal(10,2) NOT NULL,
			  `created` datetime NOT NULL
			) ENGINE='InnoDB';
		", []);

		$db->query("
			ALTER TABLE `creditnote`
			CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
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
