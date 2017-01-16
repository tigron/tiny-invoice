<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20170116_150507_Document_contract extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `document_incoming_creditnote`
			ADD `accounting_identifier` varchar(128) NOT NULL AFTER `paid`,
			ADD `supplier_identifier` varchar(128) NOT NULL AFTER `accounting_identifier`;
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
