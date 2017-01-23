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

		$price_incl = 0;
		$price_excl = 0;

		foreach ($this->get_creditnote_items() as $creditnote_item) {
			$price_incl += $creditnote_item->get_price_incl();
			$price_excl += $creditnote_item->get_price_excl();
		}
		$this->price_excl = $price_excl;
		$this->price_incl = $price_incl;
		$this->save();
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
	 * Get VAT array
	 *
	 * @access public
	 * @return array $vat
	 */
	public function get_vat_array() {
		if (!$this->customer_contact->vat_bound()) {
			return [];
		}
		$creditnote_items = $this->get_creditnote_items();
		$vat_array = [];

		foreach ($creditnote_items as $creditnote_item) {
			if (!isset($vat_array[$creditnote_item->vat])) {
				$vat_array[$creditnote_item->vat] = 0;
			}

			$vat_array[$creditnote_item->vat]+= $creditnote_item->get_price_excl();
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
	 * @param Creditnote_Method $creditnote_method
	 */
	public function send(Creditnote_Method $creditnote_method) {
		$creditnote_method->send($this);
	}
}
