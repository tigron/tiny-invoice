<?php
/**
 * User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

require_once LIB_PATH . '/model/Country.php';
require_once LIB_PATH . '/model/Customer.php';
require_once LIB_PATH . '/model/Language.php';
require_once LIB_PATH . '/model/Invoice/Item.php';
require_once LIB_PATH . '/model/Invoice/Contact.php';
require_once LIB_PATH . '/base/PDF.php';
require_once LIB_PATH . '/base/Email.php';
require_once LIB_PATH . '/model/Transfer.php';
require_once LIB_PATH . '/model/Log.php';

class Invoice {
	use Model, Get, Save, Page;

	/**
	 * Get log items
	 *
	 * @access public
	 * @return array Log $items
	 */
	public function get_logs() {
		return Log::get_by_object($this);
	}

	/**
	 * Add Invoice_Item
	 *
	 * @access public
	 * @param Invoice_Item $invoice_item
	 */
	public function add_invoice_item(Invoice_Item $invoice_item) {
		$invoice_item->invoice_id = $this->id;
		$invoice_item->save();
	}

	/**
	 * Get invoice_items
	 *
	 * @access public
	 * @return array $invoice_items
	 */
	public function get_invoice_items() {
		return Invoice_Item::get_by_invoice($this);
	}

	/**
	 * Get price
	 *
	 * @access public
	 * @return double $price
	 */
	public function get_price() {
		$invoice_items = $this->get_invoice_items();
		$price_excl = 0;
		foreach ($invoice_items as $invoice_item) {
			$price_excl += $invoice_item->price;
		}
		return $price_excl;
	}

	/**
	 * Get price incl
	 *
	 * @access public
	 * @return double $price
	 */
	public function get_price_incl() {
		$vat_array = $this->get_vat_array();
		$incl = $this->get_price();
		foreach ($vat_array as $price) {
			$incl += $price;
		}
		return $incl;
	}

	/**
	 * Get VAT array
	 *
	 * @access public
	 * @return array $vat
	 */
	public function get_vat_array() {
		$invoice_items = $this->get_invoice_items();
		$vat_array = array();
		foreach ($invoice_items as $invoice_item) {
			if (!isset($vat[$invoice_item->vat])) {
				$vat_array[$invoice_item->vat] = 0;
			}

			$vat_array[$invoice_item->vat]+= $invoice_item->price;
		}

		foreach ($vat_array as $vat => $price) {
			$vat_array[$vat] = round($price*$vat, 2);
		}

		ksort($vat_array);

		return $vat_array;
	}

	/**
	 * Render
	 *
	 * @access public
	 * @return File $pdf
	 */
	public function render() {
		$config = Config::Get();

		$pdf = new PDF('invoice');
		$pdf->assign('invoice', $this);
		$pdf->assign('company_info', $config->company_info);
		$file = $pdf->render('file', 'invoice_' . $this->id . '.pdf');
		$file->save();
		$this->file_id = $file->id;
		$this->save();
		return $file;
	}

	/**
	 * Send invoice
	 *
	 * @access public
	 */
	public function send_invoice_email() {
		$config = Config::Get();

		$mail = new Email('invoice');
		$mail->add_recipient($this->invoice_contact);
		$mail->set_sender($config->company_info['email'], $config->company_info['company']);

		if ($this->file_id == 0) {
			$this->render();
		}
		$mail->add_file($this->file);
		$mail->assign('invoice', $this);
		$mail->assign('company_info', $config->company_info);
		$mail->send();
	}

	/**
	 * Add a transfer
	 *
	 * @access public
	 * @param Transfer
	 */
	public function add_transfer(Transfer $transfer) {

		$transfer->order_id = $this->id;
		$transfer->save();

		Log::create('add', $transfer);

		if ($this->get_amount_paid() >= $this->get_price_incl()) {
			$this->mark_paid();
		}
	}

	/**
	 * Get all transfers for this invoice
	 *
	 * @access public
	 * @return array $transfers
	 */
	public function get_transfers() {
		return Transfer::get_by_invoice($this);
	}

	/**
	 * Get amount paid
	 *
	 * @access public
	 * @return double $amount
	 */
	public function get_amount_paid() {
		return Transfer::get_amount_by_invoice($this);
	}

	/**
	 * Mark paid
	 *
	 * @access private
	 */
	private function mark_paid() {
		$this->paid = true;
		$this->save();

		Translation::configure(Language::Get(), 'admin');
		Log::create(Translation::translate('Invoice marked as paid'), $this);
	}
}
