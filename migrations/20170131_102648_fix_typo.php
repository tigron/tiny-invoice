<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20170131_102648_Fix_typo extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `bank_account_statement`
			CHANGE `orinal_balance` `original_balance` decimal(10,3) NOT NULL AFTER `date`;
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
