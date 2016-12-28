<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161227_114513_Document_contract extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE `document_contract` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `document_id` int NOT NULL,
			  `from_date` date NOT NULL,
			  `to_date` date NOT NULL,
			  `customer_id` int NOT NULL,
			  `supplier_id` int NOT NULL
			) ENGINE='InnoDB';
		", []);

		$db->query("
			CREATE TABLE `document_documentation` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `document_id` int NOT NULL,
			  `customer_id` int NOT NULL,
			  `supplier_id` int NOT NULL
			) ENGINE='InnoDB';
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
