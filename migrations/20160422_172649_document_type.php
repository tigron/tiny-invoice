<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20160422_172649_Document_type extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query('
			CREATE TABLE `document_incoming_invoice` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `document_id` int NOT NULL,
			  `supplier_id` int NOT NULL,
			  `price_excl` decimal(10,2) NOT NULL,
			  `price_incl` decimal(10,2) NOT NULL,
			  `paid` tinyint NOT NULL,
			  `date` date NOT NULL,
			  `expiration_date` date NOT NULL
			);
		', []);

		$db->query('
			ALTER TABLE `document`
			ADD `classname` varchar(32) NOT NULL AFTER `id`;
		', []);

		$db->query('
			ALTER TABLE `document_incoming_invoice`
			CHANGE `supplier_id` `supplier_id` int(11) NULL AFTER `document_id`;
		', []);

		$db->query('
			ALTER TABLE `tag`
			DROP `identifier`;
		', []);

	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
