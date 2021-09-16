<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20210402_223318_Extractor_default_value extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `extractor_pdf`
			CHANGE `eval` `eval` text COLLATE 'latin1_swedish_ci' NULL AFTER `name`;
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
