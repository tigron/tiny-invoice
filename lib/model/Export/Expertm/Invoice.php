<?php
/**
 * Export_Expertm_User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Export_Expertm_Invoice extends Export_Expertm {

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		$months = $this->get_data();

		$db = Database::get();
		$ids = [];
		foreach ($months as $month) {
			$ids = array_merge($ids, $db->get_column('SELECT id FROM invoice WHERE created LIKE "' . $month . '%"', [ ]));
		}

		$invoices = [];
		foreach ($ids as $id) {
			$invoices[$id] = Invoice::get_by_id($id);
		}
		$output1 = '';
		$output2 = '';

		foreach ($invoices as $invoice) {
			$customer_contact = $invoice->customer_contact;
			$output1 .= $this->num(9, Setting::get('expertm.centralization_account_customer'));
			$output1 .= $this->num(9, 1);
			$output1 .= $this->num(9, $customer_contact->customer_contact_export_id);
			$output1 .= $this->alf(3, 'EUR');
			$output1 .= $this->alf(1, 'F');
			$output1 .= $this->num(9, $invoice->number);
			$output1 .= $this->num(8, date('dmY', strtotime($invoice->created)));
			$output1 .= $this->num(8, date('dmY', strtotime($invoice->expiration_date)));
			$output1 .= $this->cur(12, 1);
			$output1 .= $this->num(1, 0);
			$output1 .= $this->num(1, 1);
			$output1 .= $this->alf(20, '');
			$output1 .= $this->alf(20, '');
			$output1 .= $this->cur(20, $invoice->get_price_incl());
			$output1 .= $this->cur(20, $invoice->get_price_incl());
			$output1 .= $this->cur(20, 0);
			$output1 .= $this->num(2, $this->boekhoudperiode( $invoice->created ));
			$output1 .= $this->num(6, $this->btwmaand( $invoice->created ));
			$output1 .= $this->num(1, 0);
			$output1 .= "\r\n";

			$vat = 0;
			foreach ($invoice->get_invoice_vat() as $invoice_vat) {
				$vat += $invoice_vat->vat;
			}

			$i = 1;
			foreach ($invoice->get_invoice_items() as $invoice_item) {
				$output2 .= $this->num(9, $invoice_item->product_type->identifier); // Grootboekrekening
				$output2 .= $this->alf(1, 'F');										// Document soort
				$output2 .= $this->num(9, $invoice->number);						// Document nummer
				$output2 .= $this->alf(50, '');										// Referte
				$output2 .= $this->cur(20, $invoice_item->get_price_excl());		// Bedrag valuta
				$output2 .= $this->cur(20, $invoice_item->get_price_excl());		// Bedrag referentiemunt
				$output2 .= $this->alf(1, 'C');										// Code debet/credit
				if ($invoice_item->vat_rate_id !== null) {
					$ventilatie = 5;
					if ($invoice_item->vat_rate_value == 0) {
						$ventilatie = 1;
					} elseif ($invoice_item->vat_rate_value == 1) {
						$ventilatie = 2;
					} elseif ($invoice_item->vat_rate_value == 6) {
						$ventilatie = 3;
					} elseif ($invoice_item->vat_rate_value == 12) {
						$ventilatie = 4;
					}
					$output2 .= $this->num(3, $ventilatie);							// Ventilatiecode
				} else {
					if ($customer_contact->country->iso2 == 'BE') {
						/**
						 * Diplomatie:
						 * http://diplomatie.belgium.be/sites/default/files/downloads/specimenEcert.pdf
						 */
						$output2 .= $this->num(3, 102);								// Ventilatiecode
					} elseif ($customer_contact->country->european) {
						$output2 .= $this->num(3, 57);								// Ventilatiecode
					} else {
						$output2 .= $this->num(3, 70);								// Ventilatiecode
					}
				}
				$output2 .= $this->num(9, $i);										// Volgnummer
				$output2 .= "\r\n";
				$i++;
			}
			foreach ($invoice->get_invoice_vat() as $invoice_vat) {
				$output2 .= $this->num(9, 0);
				$output2 .= $this->alf(1, 'F');
				$output2 .= $this->num(9, $invoice->number);
				$output2 .= $this->alf(50, '');
				$output2 .= $this->cur(20, $invoice_vat->vat);
				$output2 .= $this->cur(20, $invoice_vat->vat);
				$output2 .= $this->alf(1, 'C');
				$output2 .= $this->num(3, 11);
				$output2 .= $this->num(9, $i);
				$output2 .= "\r\n";
				$i++;
			}

		}
		$file = \Skeleton\File\File::store('expertm_invoices_' . date('Ymd') . '_1.txt', $output1);
 		$this->file_id = $file->id;
		$this->save();

		$file = \Skeleton\File\File::store('expertm_invoices_' . date('Ymd') . '_2.txt', $output2);
		$export = new self();
		$export->file_id = $file->id;
		$export->save();
	}

	/**
	 * Generate alfa
	 *
	 * @access private
	 * @param string $name
	 * @return string $alfa
	 */
	private function generate_alfa($name) {
		$alfa = $name;
		$alfa = str_replace(' ', '', $alfa);
		$alfa = str_replace('.', '', $alfa);
		$alfa = str_replace('@', '', $alfa);
		$alfa = str_replace('&', '', $alfa);
		$alfa = str_replace('-', '', $alfa);
		$alfa = str_replace('_', '', $alfa);
		$alfa = str_replace('!', '', $alfa);
		$alfa = str_replace(',', '', $alfa);
		$alfa = str_replace('?', '', $alfa);
		$alfa = str_replace("\"", '', $alfa);
		$alfa = str_replace(';', '', $alfa);
		$alfa = str_replace('*', '', $alfa);
		$alfa = str_replace('\'', '', $alfa);
		$alfa = strtoupper($alfa);
		$alfa = trim($alfa);
		return $alfa;
	}

	/**
	 * Export VAT
	 * Export the VAT string to the ExpertM format
	 *
	 * @access private
	 * @return string $vat
	 */
	private function export_vat($vat, Country $country) {
		if ($vat == '') {
			return $vat;
		}
		if ($country->iso2 == 'BE') {
			return substr($vat, 1, 3) . '.' . substr($vat, 4, 3) . '.' . substr($vat, 7);
		} else {
			return $vat;
		}
	}

}
