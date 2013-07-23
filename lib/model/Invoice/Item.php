<?php
/**
 * Invoice_Item class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

require_once LIB_PATH . '/model/Invoice.php';

class Invoice_Item {
	use Model, Get, Save, Delete;

	/**
	 * Get by Invoice
	 *
	 * @access public
	 * @param Customer $customer
	 * @return array $invoice_items
	 */
	public static function get_by_invoice(Invoice $invoice) {
		$db = Database::Get();
		$ids = $db->getCol('SELECT id FROM invoice_item WHERE invoice_id=?', array($invoice->id));
		$invoice_items = array();
		foreach ($ids as $id) {
			$invoice_items[] = Invoice_Item::get_by_id($id);
		}
		return $invoice_items;
	}
}
