<?php
/**
 * Invoice class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Invoice {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get {
		get_info as trait_get_info;
	}
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete {
		delete as trait_delete;
	}
	use \Skeleton\Pager\Page;

	/**
	 * Generate number
	 *
	 * @access private
	 */
	public function generate_number() {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$number = $db->get_one('SELECT number FROM ' . $table . ' ORDER BY number DESC LIMIT 1', []);
		if ($number === null) {
			$number = 1;
		} else {
			$number++;
		}
		$this->number = $number;
	}

	/**
	 * Delete the invoice
	 *
	 * @access public
	 */
	public function delete() {
		$is_last = $this->is_last();

		$balances = Bank_Account_Statement_Transaction_Balance::get_by_linked_object($this);
		foreach ($balances as $balance) {
			$balance->delete();
		}
		$invoice_vats = $this->get_invoice_vat();
		foreach ($invoice_vats as $invoice_vat) {
			$invoice_vat->delete();
		}
		$invoice_items = $this->get_invoice_items();
		foreach ($invoice_items as $invoice_item) {
			$invoice_item->delete();
		}
		$transfers = $this->get_transfers();
		foreach ($transfers as $transfer) {
			$transfer->delete();
		}
		try {
			$file = $this->file;
		} catch (\Exception $e) {
			$file = null;
		}
		
		$this->file_id = null;
		$this->save();

		if ($file !== null) {
			$file->delete();
		}
				
		$this->trait_delete();

		if ($is_last) {
			// If this is the last invoice, rollback the auto-increment
			$db = Database::get();
			$db->query('ALTER TABLE `invoice` AUTO_INCREMENT=' . $this->id);
		}
	}

	/**
	 * Get info
	 *
	 * @access public
	 * @return array $info
	 */
	public function get_info() {
		$info = $this->trait_get_info();
		$invoice_items = $this->get_invoice_items();
		$info['customer_id'] = $this->customer->uuid;
		$info['customer_contact_id'] = $this->customer_contact->uuid;
		$info['invoice_items'] = [];
		foreach ($invoice_items as $invoice_item) {
			$info['invoice_items'][] = $invoice_item->get_info();
		}
		return $info;
	}

	/**
	 * Add invoice_item
	 *
	 * @access public
	 * @param Invoice_Item $invoice_item
	 * @param Bool $validate
	 * 
	 */
	public function add_invoice_item(Invoice_Item $invoice_item, Bool $validate = true) {
		$invoice_item->invoice_id = $this->id;
		$invoice_item->save();

		$price_excl = 0;

		foreach ($this->get_invoice_items() as $invoice_item) {
			$price_excl += $invoice_item->get_price_excl();
		}

		$this->price_excl = $price_excl;
		$this->generate_invoice_vat();
		$this->price_incl = $this->get_price_incl();
		$this->save($validate);
		if (!is_null($invoice_item->invoice_queue_id)) {
			$invoice_queue = Invoice_Queue::get_by_id($invoice_item->invoice_queue_id);
			$invoice_queue->processed_to_invoice_item_id = $invoice_item->id;
			$invoice_queue->save();
		}
	}

	/**
	 * Calculate VAT
	 *
	 * Re-calculate the invoice_vat lines
	 *
	 * @access public
	 */
	public function generate_invoice_vat() {
		// Depending on the mode selected, the VAT will either be calculated per
		// invoice line, or per VAT rate group.

		// Fetch the invoice_items
		$invoice_items = $this->get_invoice_items();

		// Get existing VAT lines, reset them without saving yet
		$invoice_vats = [];
		foreach (Invoice_Vat::get_by_invoice($this) as $invoice_vat) {
			$invoice_vat->base = 0;
			$invoice_vat->vat = 0;
			$invoice_vats[$invoice_vat->vat_rate_id] = $invoice_vat;
		}

		foreach ($invoice_items as $invoice_item) {
			if ($invoice_item->vat_rate_value == 0) {
				continue;
			} elseif (isset($invoice_vats[$invoice_item->vat_rate_id])) {
				$invoice_vat = $invoice_vats[$invoice_item->vat_rate_id];
			} else {
				$invoice_vat = new Invoice_Vat();
				$invoice_vat->invoice_id = $this->id;
				$invoice_vat->vat_rate_id = $invoice_item->vat_rate_id;
				$invoice_vat->rate = $invoice_item->vat_rate_value;
				$invoice_vat->base = 0;
				$invoice_vat->vat = 0;
			}

			// Check the vat mode, calculate accordingly
			if ($this->vat_mode == 'line') {
				// Calculate the VAT for each line, add the VAT per line
				$invoice_vat->base = $invoice_vat->base + $invoice_item->get_price_excl();
				$invoice_vat->vat = $invoice_vat->vat + ($invoice_item->get_price_incl() - $invoice_item->get_price_excl());
				$invoice_vat->save();

				$invoice_vats[$invoice_item->vat_rate_id] = $invoice_vat;
			} else {
				// Group all items subject to the same VAT rate, calculate VAT
				// over the sum of these at the end
				$invoice_vat->base = $invoice_vat->base + $invoice_item->get_price_excl();
				$invoice_vats[$invoice_item->vat_rate_id] = $invoice_vat;
			}
		}

		foreach ($invoice_vats as $invoice_vat) {
			// Calculate the VAT over the group, if desired
			if ($this->vat_mode == 'group') {
				$invoice_vat->vat = $invoice_vat->base * ($invoice_vat->rate / 100);
			}

			$invoice_vat->save();
		}
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
	 * Get price incl
	 *
	 * @access public
	 * @return double $price
	 */
	public function get_price_incl() {
		$incl = $this->get_price_excl();

		$invoice_vats = Invoice_Vat::get_by_invoice($this);
		foreach ($invoice_vats as $invoice_vat) {
			$incl += $invoice_vat->vat;
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
		Log::create('Transfer added', $this);
		$this->check_paid();
	}

	/**
	 * Check paid
	 *
	 * @access public
	 */
	public function check_paid() {
		if (bcsub($this->get_price_incl(), $this->get_amount_paid(), 2) <= 0) {
			$this->mark_paid();
		} else {
			if ($this->paid) {
				$this->paid = false;
				$this->save();
				Log::create('Invoice marked as unpaid', $this);
			}
		}
	}

	/**
	 * Is expired
	 *
	 * @access public
	 * @return bool $expired
	 */
	public function is_expired() {
		if ($this->paid) {
			return false;
		}
		$expiration_date = strtotime($this->expiration_date);
		if ($expiration_date < time()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get transfer amount
	 *
	 * @access public
	 * @return double $amount
	 */
	public function get_transfer_amount() {
		$amount = 0;
		foreach ($this->get_transfers() as $transfer) {
			$amount += $transfer->amount;
		}
		return $amount;
	}

	/**
	 * Get balance
	 *
	 * @access public
	 */
	public function get_balance() {
		return bcsub($this->get_price_incl(), $this->get_transfer_amount(), 2);
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
	 * @return array Log $items
	 */
	public function get_logs() {
		return Log::get_by_object($this);
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
	 * Get Invoice_Vat
	 *
	 * @access public
	 * @return array Invoice_Vat
	 */
	public function get_invoice_vat() {
		return Invoice_Vat::get_by_invoice($this);
	}

	/**
	 * Get amount paid
	 *
	 * @access public
	 * @return double $amount
	 */
	public function get_ogm($raw = false) {
		if (!empty($this->ogm)) {
			return $this->ogm;
		}

		$number = $this->number;
		$modulo = $number - ((int)($number/97)*97);
		if($modulo == 0) {
			$modulo = 97;
		}

		$ogm = str_pad($number, 10, '0', STR_PAD_LEFT).str_pad($modulo, 2, '0', STR_PAD_LEFT);

		$this->ogm = '+++' . substr($ogm,0,3).'/'.substr($ogm,3,4).'/'.substr($ogm,7) . '+++';
		$this->save();

		if($raw) {
			return $ogm;
		}

		return $this->ogm;
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

		$pdf = new Pdf('invoice', $this->customer->language);
		$pdf->assign('invoice', $this);
		$file = $pdf->render('file', 'invoice_' . $this->number . '.pdf');
		$file->save();
		$this->file_id = $file->id;
		$this->save();

		return $file;
	}

	/**
	 * Send
	 *
	 * @access public
	 * @param Invoice_Method $invoice_method
	 */
	public function send(Invoice_Method $invoice_method = null) {
		if ($invoice_method === null) {
			$invoice_method = $this->customer_contact->invoice_method;
		}
		$invoice_method->send($this);
	}

	/**
	 * schedule Send
	 *
	 * @access public
	 * @param Invoice_Method $invoice_method
	 */
	public function schedule_send() {
		$transaction = new Transaction_Invoice_Send();
		$data = [
			'id' => $this->id
		];
		$transaction->data = json_encode($data);
		$transaction->schedule();
	}

	/**
	 * Is this the last invoice
	 *
	 * @access private
	 * @return boolean $last
	 */
	public function is_last() {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM invoice ORDER BY id DESC LIMIT 1', []);
		if ($id === $this->id) {
			return true;
		}
		return false;
	}

	/**
	 * Validate data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = null) {
		$errors = [];
		$required_fields = ['number'];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] === '') {
				$errors[$required_field] = 'required';
			}
		}

		$max_length_fieds = ['reference', 'internal_reference'];
		foreach ($max_length_fieds as $max_length_fied) {
			if (strlen($this->details[$max_length_fied]) > 64) {
				$errors[$max_length_fied] = 'too_long';
			}
		}

		$total_price = 0;
		foreach($this->get_invoice_items() as $invoice_item) {
			$total_price += $invoice_item->get_price_excl();
		}

		if ($total_price == 0) {
			$errors[] = 'free';
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get expired invoices for which the customer should receive a reminder
	 *
	 * @access public
	 * @return array Invoice $items
	 */
	public static function get_remindable() {
		$db = Database::get();
		$window = Setting::get('invoice_reminder_email_window');
		if (is_null($window)) {
			$window = 7;
		}

		$ids = $db->get_column('
			SELECT id
			FROM
				invoice
			WHERE
				1
				AND paid = 0
				AND send_reminder_mail = 1
				AND expiration_date < NOW()
				AND DATEDIFF(now(), expiration_date) >= ?
		', [ $window ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Mark paid
	 *
	 * @access private
	 */
	private function mark_paid() {
		$this->paid = true;
		$this->save();
		Log::create('Invoice marked as paid', $this);
	}

	/**
	 * Get expired by Customer Contact
	 *
	 * @access public
	 * @param Customer_Contact $customer_contact
	 * @return array $invoices
	 */
	public static function get_expired_by_customer_contact(Customer_Contact $customer_contact) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM invoice WHERE customer_contact_id=? AND paid=0 AND expiration_date < NOW()', [ $customer_contact->id ]);
		$invoices = [];
		foreach ($ids as $id) {
			$invoices[] = self::get_by_id($id);
		}
		return $invoices;
	}

	/**
	 * Get expired by Customer
	 *
	 * @access public
	 * @param customer $customer
	 * @return array $invoices
	 */
	public static function get_expired_by_customer(Customer $customer) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM invoice WHERE customer_id=? AND paid=0 AND expiration_date < NOW()', [ $customer->id ]);
		$invoices = [];
		foreach ($ids as $id) {
			$invoices[] = self::get_by_id($id);
		}
		return $invoices;
	}

	/**
	 * Get by number
	 *
	 * @access public
	 * @param int $number
	 * @return Invoice $invoice
	 */
	public static function get_by_number($number) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM invoice WHERE number=?', [ $number ]);
		if ($id === null) {
			throw new \Exception('Invoice with number ' . $number . ' not found');
		}
		return self::get_by_id($id);
	}

	/**
	 * Get by OGM
	 *
	 * @access public
	 * @param string $ogm
	 * @return Invoice $invoice
	 */
	public static function get_by_ogm($ogm) {
		preg_match("/\+\+\+(\d{3}\/\d{4}\/\d{5})\+\+\+/", $ogm, $output_array);
		if (count($output_array) == 0) {
			throw new Exception('This is an incorrect ogm');
		}
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM invoice WHERE ogm=?', [ $ogm ]);
		if ($id === null) {
			throw new Exception('No invoice found with ogm "' . $ogm . '"');
		}
		return self::get_by_id($id);
	}
}
