<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20160510_232425_Incoming_creditnote extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE `document_incoming_creditnote` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `document_id` int(11) NOT NULL,
			  `supplier_id` int(11) DEFAULT NULL,
			  `price_excl` decimal(10,2) NOT NULL,
			  `price_incl` decimal(10,2) NOT NULL,
			  `paid` tinyint(4) NOT NULL,
			  `expiration_date` date NOT NULL,
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
