<?php
/**
 * Invoice class
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Invoice {
	use Get, Delete, Model, Save, Page;

	/**
	 * Generate number
	 *
	 * @access private
	 */
	public function generate_number() {
		$db = Database::Get();
		$number = $db->getOne('SELECT number FROM invoice ORDER BY number DESC LIMIT 1', []);
		if ($number === null) {
			$number = 1;
		} else {
			$number++;
		}
		$this->number = $number;
	}

	/**
	 * Add invoice_item
	 *
	 * @access public
	 * @param Invoice_Item $invoice_item
	 */
	public function add_invoice_item(Invoice_Item $invoice_item) {
		$invoice_item->invoice_id = $this->id;
		$invoice_item->save();

		$price_incl = 0;
		$price_excl = 0;

		foreach ($this->get_invoice_items() as $invoice_item) {
			$price_incl += $invoice_item->get_price_incl();
			$price_excl += $invoice_item->get_price_excl();
		}
		$this->price_excl = $price_excl;
		$this->price_incl = $price_incl;
		$this->save();
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
	public function get_price_excl() {
		$invoice_items = $this->get_invoice_items();
		$price_excl = 0;
		foreach ($invoice_items as $invoice_item) {
			$price_excl += $invoice_item->get_price_excl();
		}
		return $price_excl;
	}

	/**
	 * Get VAT array
	 *
	 * @access public
	 * @return array $vat
	 */
	public function get_vat_array() {
		if (!$this->invoice_contact->vat_bound()) {
			return [];
		}
		$invoice_items = $this->get_invoice_items();
		$vat_array = [];

		foreach ($invoice_items as $invoice_item) {
			if (!isset($vat_array[$invoice_item->vat])) {
				$vat_array[$invoice_item->vat] = 0;
			}

			$vat_array[$invoice_item->vat]+= $invoice_item->get_price_excl();
		}

		foreach ($vat_array as $vat => $price) {
			$vat_array[$vat] = round($price*($vat/100), 2);
		}

		ksort($vat_array);


		return $vat_array;
	}


	/**
	 * Get price incl
	 *
	 * @access public
	 * @return double $price
	 */
	public function get_price_incl() {
		if (!$this->invoice_contact->vat_bound()) {
			return $this->get_price_excl();
		}
		$vat_array = $this->get_vat_array();
		$incl = $this->get_price_excl();
		foreach ($vat_array as $price) {
			$incl += $price;
		}
		return $incl;
	}

	/**
	 * Add a transfer
	 *
	 * @access public
	 * @param Transfer
	 */
	public function add_transfer(Transfer $transfer) {
		$transfer->invoice_id = $this->id;
		$transfer->save();

		Object_Log::create('add', $transfer);

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
	 * Get all logs for this invoice
	 *
	 * @access public
	 * @return array Object_Log $items
	 */
	public function get_logs() {
		return Object_Log::get_by_object($this);
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
	 * Get amount paid
	 *
	 * @access public
	 * @return double $amount
	 */
	public function get_ogm($raw = false){
		$number = $this->number;
		$modulo = $number - ((int)($number/97)*97);
		if($modulo == 0) {
			$modulo = 97;
		}

		$ogm = str_pad($number, 10, '0', STR_PAD_LEFT).str_pad($modulo, 2, '0', STR_PAD_LEFT);
		if($raw) {
			return $ogm;
		}

		return '+++' . substr($ogm,0,3).'/'.substr($ogm,3,4).'/'.substr($ogm,7) . '+++';
	}

	/**
	 * Get invoice pdf
	 *
	 * @access public
	 * @return File $file
	 */
	public function get_pdf() {
		if ($this->file_id > 0) {
			return $this->file;
		}

		$pdf = new PDF('invoice', $this->customer->language);
		$pdf->assign('invoice', $this);
		$settings = Setting::get_as_array();
		if (isset($settings['country_id'])) {
			$settings['country'] = Country::get_by_id($settings['country_id']);
		}
		$pdf->assign('settings', $settings);

		$file = $pdf->render('file', 'invoice_' . $this->number . '.pdf');
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
		if ($this->invoice_contact->email == '') {
			$mail->add_recipient($this->customer);
		} else {
			$mail->add_recipient($this->invoice_contact);
			if ($this->invoice_contact->email != $this->customer->email) {
				$mail->add_recipient($this->customer, 'cc');

			}
		}
		try {
			$email_from = Setting::get_by_name('email')->value;
			$company = Setting::get_by_name('company')->value;
			$mail->set_sender($email_from, $company);
		} catch (Exception $e) {
		}

		$mail->add_file($this->get_pdf());
		$mail->assign('invoice', $this);

		$settings = Setting::get_as_array();
		if (isset($settings['country_id'])) {
			$settings['country'] = Country::get_by_id($settings['country_id']);
		}
		$mail->assign('settings', $settings);
		$mail->send();
	}


	/**
	 * Mark paid
	 *
	 * @access private
	 */
	private function mark_paid() {
		$this->paid = true;
		$this->save();
	}
}
