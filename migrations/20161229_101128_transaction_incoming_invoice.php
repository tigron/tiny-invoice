<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161229_101128_Transaction_incoming_invoice extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `document_incoming_invoice`
			ADD `balanced` tinyint NOT NULL DEFAULT '0';
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
