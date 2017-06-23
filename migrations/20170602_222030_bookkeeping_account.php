<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20170602_222030_Bookkeeping_account extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE `bookkeeping_account` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `number` varchar(16) NOT NULL,
			  `name` varchar(128) NOT NULL
			);
		", []);

		$db->query('UPDATE permission SET identifier="admin.bookkeeping" WHERE identifier="admin.financial"', []);

	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
