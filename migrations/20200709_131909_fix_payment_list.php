<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20200709_131909_Fix_payment_list extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `payment_list`
			CHANGE `created` `created` int(11) NULL AFTER `export_id`,
			ADD `created_new` datetime NOT NULL;
		", []);

		$db->query("
			UPDATE
			payment_list, export
			SET payment_list.created_new=export.created
			WHERE payment_list.export_id=export.id
		", []);

		$db->query("
			ALTER TABLE `payment_list`
			DROP `created`;
		", []);

		$db->query("
			ALTER TABLE `payment_list`
			CHANGE `created_new` `created` datetime NOT NULL AFTER `export_id`;
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
