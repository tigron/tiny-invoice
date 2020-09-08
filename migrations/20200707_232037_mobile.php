<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20200707_232037_Mobile extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("
			CREATE TABLE `mobile` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `user_id` int(11) NOT NULL,
			  `token` varchar(128) NOT NULL,
			  `registered` datetime NULL,
			  `last_seen` datetime NULL,
			  `created` datetime NOT NULL,
			  FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
			);
		", []);
		
		$db->query("
			ALTER TABLE `mobile`
			ADD UNIQUE `token` (`token`);		
		", []);
		
		$db->query("
			ALTER TABLE `mobile`
			CHANGE `token` `token` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `user_id`;		
		", []);
		
		$db->query("
			ALTER TABLE `mobile`
			ADD `name` varchar(128) NULL AFTER `user_id`;		
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
