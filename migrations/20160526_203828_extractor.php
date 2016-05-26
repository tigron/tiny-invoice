<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20160526_203828_Extractor extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE `extractor` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(128) NOT NULL,
			  `created` datetime NOT NULL,
			  `document_id` int(11) NOT NULL,
			  `eval` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		", []);

		$db->query("
			CREATE TABLE `extractor_fingerprint` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `extractor_id` int(11) NOT NULL,
			  `x` int(11) NOT NULL,
			  `y` int(11) NOT NULL,
			  `height` int(11) NOT NULL,
			  `width` int(11) NOT NULL,
			  `sort` int(11) NOT NULL,
			  `value` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
