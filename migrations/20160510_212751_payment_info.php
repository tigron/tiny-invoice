<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20160510_212751_Payment_info extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `document_incoming_invoice`
			ADD `payment_message` varchar(255) NOT NULL,
			ADD `payment_structured_message` varchar(128) NOT NULL AFTER `payment_message`;
		", []);

		$db->query("
			ALTER TABLE `supplier`
			ADD `iban` varchar(64) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `accounting_identifier`;
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
