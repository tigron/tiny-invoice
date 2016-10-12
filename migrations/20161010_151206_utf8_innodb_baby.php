<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161010_151206_Utf8_innodb_baby extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$tables = $db->get_column('SHOW tables;', []);

		$db->query("
			ALTER TABLE `language`
			DROP INDEX `name_short`;
		", []);

		foreach ($tables as $table) {
			$db->query("
				ALTER TABLE `" . $table . "`
				ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';
			", []);
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
