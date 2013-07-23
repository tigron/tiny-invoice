<?php
/**
 * Invoice_Contact class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

require_once LIB_PATH . '/model/Invoice.php';

class Invoice_Contact {
	use Model, Get, Save, Delete, Page;

	/**
	 * Get VAT formatted
	 *
	 * @access public
	 * @return string $vat
	 */
	public function get_vat_formatted() {
		if (!isset($this->details['vat']) OR $this->details['vat'] == '') {
			return '';
		}

		if ($this->country->iso2 == 'BE') {
			return 'BE ' . substr($this->vat, 0, 4) . '.' . substr($this->vat, 4, 3) . '.' . substr($this->vat, 7);
		} else {
			return $this->country->iso2 . ' ' . $this->vat;
		}
	}

	/**
	 * Get by Customer
	 *
	 * @access public
	 * @param Customer $customer
	 * @return array $invoice_contacts
	 */
	public static function get_by_customer(Customer $customer) {
		$db = Database::Get();
		$ids = $db->getCol('SELECT id FROM invoice_contact WHERE customer_id=?', array($customer->id));
		$invoice_contacts = array();
		foreach ($ids as $id) {
			$invoice_contacts[] = Invoice_Contact::get_by_id($id);
		}
		return $invoice_contacts;
	}
}
