<?php
/**
 * Invoice class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Invoice {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
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
	 * Add invoice_item
	 *
	 * @access public
	 * @param Invoice_Item $invoice_item
	 */
	public function add_invoice_item(Invoice_Item $invoice_item) {
		$invoice_item->invoice_id = $this->id;
		$invoice_item->save();

		$price_excl = 0;

		foreach ($this->get_invoice_items() as $invoice_item) {
			$price_excl += $invoice_item->get_price_excl();
		}
		$this->price_excl = $price_excl;
		$this->price_incl = $this->get_price_incl();
		$this->save();

		if (!is_null($invoice_item->invoice_queue_id)) {
			$invoice_queue = Invoice_Queue::get_by_id($invoice_item->invoice_queue_id);
			$invoice_queue->processed_to_invoice_item_id = $invoice_item->id;
			$invoice_queue->save();
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
	 * Get VAT array
	 *
	 * @access public
	 * @return array $vat
	 */
	public function get_vat_array() {
		if (!$this->customer_contact->vat_bound()) {
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
		if (!$this->customer_contact->vat_bound()) {
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

		if (bcsub($this->get_price_incl(), $this->get_amount_paid(), 2) <= 0) {
			$this->mark_paid();
		}
		Log::create('Transfer added', $this);
	}

	/**
	 * Is expired
	 *
	 * @access public
	 * @return bool $expired
	 */
	public function is_expired() {
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
		$transaction->save();
	}

	/**
	 * Get expired invoices for which the customer should receive a reminder
	 *
	 * @access public
	 * @return array Invoice $items
	 */
	public static function get_remindable() {
		$db = Database::get();
		$ids = $db->get_column('
			SELECT id
			FROM
				invoice
			WHERE
				1
				AND paid = 0
				AND send_reminder_mail = 1
				AND expiration_date < NOW()
				AND DATEDIFF(now(), expiration_date) >= 7
		');

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
}
