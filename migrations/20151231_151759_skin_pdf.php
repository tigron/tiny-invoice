<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20151231_151759_Skin_pdf extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query('
			CREATE TABLE `skin_pdf` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `path` varchar(64) NOT NULL,
			  `description` varchar(128) NOT NULL
			);
		');

		Skin_Pdf::synchronize();
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
