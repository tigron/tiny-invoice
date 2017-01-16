<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161229_144155_Invoice_contact_reference extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `customer_contact`
			ADD `reference` varchar(64) COLLATE 'utf8_general_ci' NOT NULL AFTER `vat`;
		", []);

		$db->query("
			ALTER TABLE `customer_contact`
			ADD `alias` varchar(128) NOT NULL AFTER `language_id`;
		", []);

		$ids = $db->get_column('SELECT id FROM customer_contact', []);
		foreach ($ids as $id) {
			$contact = Customer_Contact::get_by_id($id);
			$contact->alias = $contact->get_display_name();
			$contact->save(false);
		}
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
