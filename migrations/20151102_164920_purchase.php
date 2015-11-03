<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20151102_164920_Purchase extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query('ALTER TABLE `purchase` ADD `date` datetime NULL AFTER `paid`;');
		$db->query('ALTER TABLE `purchase` ADD `updated` datetime NULL;');
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
