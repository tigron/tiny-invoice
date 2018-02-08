<?php
/**
 * Export_Expertm_User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;
use Cocur\Slugify\Slugify;

class Export_Expertm_User extends Export_Expertm {

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {

		$output = '';

		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM customer_contact_export', []);

		foreach ($ids as $id) {
			$customer_contact_export = Customer_Contact_Export::get_by_id($id);
			if ($customer_contact_export->company == '') {
				$name = $customer_contact_export->firstname . ' ' . $customer_contact_export->lastname;
			} else {
				$name = $customer_contact_export->company;
			}

			$output .= $this->num(9, $customer_contact_export->id);
			$output .= $this->alf(2, $customer_contact_export->country->iso2);
			$output .= $this->alf(10, $this->generate_alfa($name));
			$output .= $this->alf(30, $name);
			$output .= $this->alf(60, $customer_contact_export->street);
			$output .= $this->alf(10, $customer_contact_export->housenumber);
			$output .= $this->alf(5, '');
			$output .= $this->alf(10, $customer_contact_export->zipcode);
			$output .= $this->alf(50, $customer_contact_export->city);
			$output .= $this->num(1, 1);
			$output .= $this->alf(3, 'EUR');
			$output .= $this->num(1, $customer_contact_export->vat_bound());
			if ($customer_contact_export->vat == '') {
				$output .= $this->alf(1, 'G');
			} else {
				$output .= $this->alf(1, 'B');
			}

			$output .= $this->alf(30, $this->export_vat($customer_contact_export->vat, $customer_contact_export->country));
			$output .= '00' . $this->alf(90, '');
			$output .= $this->alf(200, '');
			$output .= $this->num(9, Setting::get('expertm.centralization_account_sale'));
			$output .= $this->num(8, 0);
			$output .= $this->num(1, 0);
			$output .= $this->num(1, 0);
			$output .= $this->alf(30, '');
			$output .= $this->alf(20, '');
			$output .= $this->num(1, 0);
			$output .= "\r\n";
		}
		$file = \Skeleton\File\File::store('expertm_customers_' . date('Ymd') . '.txt', $output);
 		$this->file_id = $file->id;
		$this->save();
	}

	/**
	 * Generate alfa
	 *
	 * @access private
	 * @param string $name
	 * @return string $alfa
	 */
	private function generate_alfa($name) {
		$slugify = new Slugify();
		$slug = $slugify->slugify($name);
		$slug = str_replace('-', '', $slug);
		$slug = strtoupper($slug);
		return $slug;
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
