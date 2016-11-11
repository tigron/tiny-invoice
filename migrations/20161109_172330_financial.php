<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161109_172330_Financial extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `bank_account_statement_transaction`
			ADD `balanced` tinyint NOT NULL DEFAULT '0';
		", []);

		$db->query("
			ALTER TABLE `bank_account_statement_transaction_balance`
			CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
		", []);

		$ids = $db->get_column('SELECT id FROM invoice', []);

		foreach ($ids as $id) {
			$invoice = Invoice::get_by_id($id);
			$price_incl = $invoice->get_price_incl();
			if ($price_incl != $invoice->price_incl) {
				$invoice->price_incl = $invoice->get_price_incl();
				$invoice->save();
			}
		}

		$db->query("
			ALTER TABLE `transfer`
			ADD `bank_account_statement_transaction_id` int NOT NULL AFTER `amount`;
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
