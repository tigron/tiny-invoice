<?php
/**
 * Invoice_Vat class
 */

use \Skeleton\Database\Database;

class Invoice_Vat {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * Get by Invoice and Vat_Rate
	 *
	 * @access public
	 * @param Invoice
	 * @param Vat_Rate
	 * @return array Invoice_Vat $invoice_vats
	 */
	public static function get_by_invoice_vat_rate(Invoice $invoice, Vat_Rate $vat_rate) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE invoice_id = ? AND vat_rate_id = ?', [ $invoice->id, $vat_rate->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get by Invoice
	 *
	 * @access public
	 * @param Invoice
	 * @return array Invoice_Vat $invoice_vats
	 */
	public static function get_by_invoice(Invoice $invoice) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE invoice_id = ?', [ $invoice->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}
}
