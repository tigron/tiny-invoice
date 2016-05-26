<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20160513_213958_Invoice_accounting_identifier extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `document_incoming_invoice`
			ADD `accounting_identifier` varchar(128) COLLATE 'latin1_swedish_ci' NOT NULL;
		", []);

		$db->query("
			ALTER TABLE `document_incoming_invoice`
			ADD `supplier_identifier` varchar(128) COLLATE 'latin1_swedish_ci' NOT NULL;
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
