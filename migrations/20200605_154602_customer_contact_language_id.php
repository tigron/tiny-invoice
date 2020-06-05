<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20200605_154602_Customer_contact_language_id extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM customer_contact
								WHERE language_id = 0');

		foreach ($ids as $id) {
			$customer_contact = Customer_Contact::get_by_id($id);
			$customer_contact->language_id = $customer_contact->customer->language_id;
			$customer_contact->save();
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
