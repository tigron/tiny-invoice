<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20201019_161125_Setting_unique_key extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			DELETE setting
	   		FROM setting
	  			INNER JOIN (
	     			SELECT MAX(id) as last_id, name
	       			FROM setting
	      			GROUP BY name
	     			HAVING COUNT(1) > 1) duplic ON duplic.name = setting.name
	  		WHERE setting.id < duplic.last_id;
		", []);
		$db->query("ALTER TABLE `setting` ADD UNIQUE `name` (`name`);", []);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
