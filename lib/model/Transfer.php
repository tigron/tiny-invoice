<?php
/**
 * Transfer class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

define('TRANSFER_TYPE_PAYMENT_MANUAL',       1);
define('TRANSFER_TYPE_PAYMENT_CREDITNOTE',   2);
define('TRANSFER_TYPE_PAYMENT_WIRETRANSFER', 10);

use \Skeleton\Database\Database;

class Transfer {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;

	/**
	 * Get transfers by invoice
	 *
	 * @access public
	 * @param Invoice $invoice
	 * @return array Transfer $items
	 */
	public static function get_by_invoice(Invoice $invoice) {
		$table = self::trait_get_database_table();
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE invoice_id = ?', [ $invoice->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get transfer amount by invoice
	 *
	 * @access public
	 * @param Invoice $invoice
	 * @return descimal $amount
	 */
	public static function get_amount_by_invoice(Invoice $invoice) {
		$table = self::trait_get_database_table();
		$db = Database::get();
		$amount = $db->get_one('SELECT SUM(amount) FROM ' . $table . ' WHERE invoice_id = ?', [ $invoice->id ]);
		return $amount;
	}
}
