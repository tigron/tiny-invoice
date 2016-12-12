<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161212_095028_Invoice_ogm extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("ALTER TABLE `invoice` CHANGE `ogm` `ogm` varchar(20) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `price_incl`;", []);
		$db->query("UPDATE invoice SET ogm = CONCAT(ogm, '++') ;", []);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
