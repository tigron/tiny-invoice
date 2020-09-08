<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20200907_173755_Add_payment_message_to_incommig_creditnotes extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query('
			ALTER TABLE `document_incoming_creditnote`
			ADD `payment_message` varchar(255) NOT NULL AFTER `expiration_date`,
			ADD `payment_structured_message` varchar(128) NOT NULL AFTER `payment_message`;
		');
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
