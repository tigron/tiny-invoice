<?php
/**
 * Creditnote class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Creditnote {
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
	 * Add creditnote_item
	 *
	 * @access public
	 * @param Creditnote_Item $creditnote_item
	 */
	public function add_creditnote_item(Creditnote_Item $creditnote_item) {
		$creditnote_item->creditnote_id = $this->id;
		$creditnote_item->save();

		$price_excl = 0;

		foreach ($this->get_creditnote_items() as $creditnote_item) {
			$price_excl += $creditnote_item->get_price_excl();
		}

		$this->price_excl = $price_excl;
		$this->generate_creditnote_vat();
		$this->price_incl = $this->get_price_incl();
		$this->save();
	}

	/**
	 * Calculate VAT
	 *
	 * Re-calculate the creditnote_vat lines
	 *
	 * @access public
	 */
	public function generate_creditnote_vat() {
		// Depending on the mode selected, the VAT will either be calculated per
		// creditnote line, or per VAT rate group.

		// Fetch the creditnote_items
		$creditnote_items = $this->get_creditnote_items();

		// Get existing VAT lines, reset them without saving yet
		$creditnote_vats = [];
		foreach (Creditnote_Vat::get_by_creditnote($this) as $creditnote_vat) {
			$creditnote_vat->base = 0;
			$creditnote_vat->vat = 0;
			$creditnote_vats[$creditnote_vat->vat_rate_id] = $creditnote_vat;
		}

		foreach ($creditnote_items as $creditnote_item) {
			if ($creditnote_item->vat_rate_value == 0) {
				continue;
			} elseif (isset($creditnote_vats[$creditnote_item->vat_rate_id])) {
				$creditnote_vat = $creditnote_vats[$creditnote_item->vat_rate_id];
			} else {
				$creditnote_vat = new Creditnote_Vat();
				$creditnote_vat->creditnote_id = $this->id;
				$creditnote_vat->vat_rate = $creditnote_item->vat_rate;
				$creditnote_vat->rate = $creditnote_item->vat_rate_value;
				$creditnote_vat->base = 0;
				$creditnote_vat->vat = 0;
			}

			// Check the vat mode, calculate accordingly
			if ($this->vat_mode == 'line') {
				// Calculate the VAT for each line, add the VAT per line
				$creditnote_vat->base = $creditnote_vat->base + $creditnote_item->get_price_excl();
				$creditnote_vat->vat = $creditnote_vat->vat + ($creditnote_item->get_price_incl() - $creditnote_item->get_price_excl());
				$creditnote_vat->save();

				$creditnote_vats[$creditnote_item->vat_rate_id] = $creditnote_vat;
			} else {
				// Group all items subject to the same VAT rate, calculate VAT
				// over the sum of these at the end
				$creditnote_vat->base = $creditnote_vat->base + $creditnote_item->get_price_excl();
				$creditnote_vats[$creditnote_item->vat_rate_id] = $creditnote_vat;
			}
		}

		foreach ($creditnote_vats as $creditnote_vat) {
			// Calculate the VAT over the group, if desired
			if ($this->vat_mode == 'group') {
				$creditnote_vat->vat = $creditnote_vat->base * ($creditnote_vat->rate / 100);
			}

			$creditnote_vat->save();
		}
	}

	/**
	 * Get creditnote_items
	 *
	 * @access public
	 * @return array $creditnote_items
	 */
	public function get_creditnote_items() {
		return Creditnote_Item::get_by_creditnote($this);
	}

	/**
	 * Get creditnote_vat
	 *
	 * @access public
	 * @return array Creditnote_Vat
	 */
	public function get_creditnote_vat() {
		return Creditnote_Vat::get_by_creditnote($this);
	}

	/**
	 * Get price
	 *
	 * @access public
	 * @return double $price
	 */
	public function get_price_excl() {
		$creditnote_items = $this->get_creditnote_items();
		$price_excl = 0;
		foreach ($creditnote_items as $creditnote_item) {
			$price_excl += $creditnote_item->get_price_excl();
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

		$creditnote_vats = Creditnote_Vat::get_by_creditnote($this);
		foreach ($creditnote_vats as $creditnote_vat) {
			$incl += $creditnote_vat->vat;
		}

		return $incl;
	}

	/**
	 * Get all logs for this creditnote
	 *
	 * @access public
	 * @return array $items
	 */
	public function get_logs() {
		return Log::get_by_object($this);
	}

	/**
	 * Get creditnote pdf
	 *
	 * @access public
	 * @return File $file
	 */
	public function get_pdf() {
		if ($this->file_id > 0) {
			return $this->file;
		}

		$pdf = new Pdf('creditnote', $this->customer->language);
		$pdf->assign('creditnote', $this);
		$file = $pdf->render('file', 'creditnote_' . $this->number . '.pdf');
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
		$invoice_method->send_creditnote($this);
	}
}
