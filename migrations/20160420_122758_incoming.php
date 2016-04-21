<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20160420_122758_Incoming extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query('
			CREATE TABLE `incoming` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `created` datetime NOT NULL,
			  `subject` varchar(255) NOT NULL
			);
		', []);

		$db->query('
			ALTER TABLE `incoming`
			ADD `file_id` int NOT NULL;
		', []);

		$db->query('
			CREATE TABLE `incoming_page` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `incoming_id` int NOT NULL,
			  `file_id` int NOT NULL
			);
		', []);

		$db->query('
			ALTER TABLE `incoming_page`
			ADD `preview_file_id` int(11) NOT NULL;
		', []);

		$db->query('
			DELETE FROM setting WHERE  name="mailscanner_tag_id"
		', []);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
