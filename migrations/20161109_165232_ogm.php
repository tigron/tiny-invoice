<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161109_165232_Ogm extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `invoice`
			ADD `ogm` varchar(18) NOT NULL AFTER `price_incl`;
		", []);

		$ids = $db->get_column('SELECT id FROM invoice', []);
		foreach ($ids as $id) {
			$invoice = Invoice::get_by_id($id);
			$invoice->get_ogm();
		}

	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
