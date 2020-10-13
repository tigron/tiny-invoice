<?php
/**
 * Database migration class
 *
 */
use \Skeleton\Database\Database;

class Migration_20201001_151200_Default_values_fix extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `document_incoming_invoice`
			CHANGE `payment_message` `payment_message` varchar(255) COLLATE 'latin1_swedish_ci' NULL AFTER `expiration_date`,
			CHANGE `payment_structured_message` `payment_structured_message` varchar(128) COLLATE 'latin1_swedish_ci' NULL AFTER `payment_message`,
			CHANGE `accounting_identifier` `accounting_identifier` varchar(128) COLLATE 'latin1_swedish_ci' NULL AFTER `payment_structured_message`,
			CHANGE `supplier_identifier` `supplier_identifier` varchar(128) COLLATE 'latin1_swedish_ci' NULL AFTER `accounting_identifier`;
		", []);


		$db->query("
			ALTER TABLE `document_incoming_creditnote`
			CHANGE `accounting_identifier` `accounting_identifier` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `paid`,
			CHANGE `supplier_identifier` `supplier_identifier` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `accounting_identifier`,
			CHANGE `payment_message` `payment_message` varchar(255) COLLATE 'utf8_unicode_ci' NULL AFTER `expiration_date`,
			CHANGE `payment_structured_message` `payment_structured_message` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `payment_message`;
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
