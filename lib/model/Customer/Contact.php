<?php
/**
 * Customer_Contact class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Customer_Contact {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Uuid;
	use \Skeleton\Object\Save {
		save as trait_save;
	}
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Save
	 *
	 * @access public
	 * @param boolean $validate
	 */
	public function save($validate = true) {
		$this->trait_save($validate);
		$this->export();
	}

	/**
	 * Get VAT formatted
	 *
	 * @access public
	 * @return string $vat
	 */
	public function get_vat_formatted() {
		return Vat::format($this->details['vat'], $this->country);
	}

	/**
	 * Validate user data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = null) {
		$errors = [];
		$required_fields = [ 'firstname', 'lastname', 'email', 'street', 'housenumber', 'city', 'zipcode', 'country_id' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (!Validation::validate_email($this->details['email'])) {
			$errors['email'] = 'syntax error';
		}

		if (isset($this->details['vat']) AND $this->details['vat'] != '') {
			if (!Validation::validate_vat($this->details['vat'], $this->country)) {
				$errors['vat'] = 'incorrect';
			}
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get expired_invoices
	 *
	 * @access public
	 * @return array $invoices
	 */
	public function get_expired_invoices() {
		return Invoice::get_expired_by_customer_contact($this);
	}

	/**
	 * Get invoice reminder pdf
	 *
	 * @access public
	 * @return File $pdf
	 */
	public function get_invoice_reminder_pdf() {
		$pdf = new Pdf('invoice_reminder', $this->language);
		$pdf->assign('customer_contact', $this);
		$pdf->assign('invoices', $this->get_expired_invoices());
		$file = $pdf->render('file', 'reminder_' . $this->get_identifier() . '_' . date('Ymd') . '.pdf');
		$file->save();

		return $file;
	}

	/**
	 * Check if this contact is VAT bound
	 *
	 * @return bool
	 * @access public
	 */
	public function vat_bound() {
		if (isset($this->details['vat_bound']) AND $this->vat_bound == 0) {
			return false;
		} elseif (isset($this->details['vat_bound']) AND $this->vat_bound == 1) {
			return true;
		}  else {
			/* If the customer:
			 *  - Is a European resident
			 *  - Not a Belgian citizen
			 *  - Has a VAT number
			 * he is not bound to VAT
			 */
			if ($this->country->european == true && $this->country->iso2 != 'BE' && $this->vat != '') {
				return false;
			}

			/* If the customer is not a European resident, he is not VAT bound */
			else if ($this->country->european == false) {
				return false;
			}

			/* If the customer is anything else, he is VAT bound */
			else {
				return true;
			}
		}
	}

	/**
	 * Get Vat Rate Country
	 *
	 * @access public
	 * @param Vat_Rate $vat_rate
	 * @return Vat_Rate_Country $vat_rate_country
	 */
	public function get_vat_rate_country(Vat_Rate $vat_rate) {
		if (isset($this->details['vat_bound']) AND $this->vat_bound == 0) {
			throw new Exception('No Vat_Rate_Country');
		} elseif (isset($this->details['vat_bound']) AND $this->vat_bound == 1) {
			return Vat_Rate_Country::get_by_vat_rate_country($vat_rate, $this->country);
		}  else {
			/* If the customer:
			 *  - Is a European resident
			 *  - Not a Belgian citizen
			 *  - Has a VAT number
			 * he is not bound to VAT
			 */
			if ($this->country->european == true && $this->country->iso2 != 'BE' && $this->vat != '') {
				throw new Exception('No Vat_Rate_Country');
			}

			/* If the customer is not a European resident, he is not VAT bound */
			else if ($this->country->european == false) {
				throw new Exception('No Vat_Rate_Country');
			}

			/* If the customer is anything else, he is VAT bound */
			else {
				return Vat_Rate_Country::get_by_vat_rate_country($vat_rate, $this->country);
			}
		}
	}

	/**
	 * Get VAT
	 *
	 * @access public
	 * @return double $vat
	 */
	public function get_vat(Vat_Rate $vat_rate) {
		try {
			$vat_rate_country = $this->get_vat_rate_country($vat_rate);
			return $vat_rate_country->vat;
		} catch (Exception $e) {
			return 0;
		}
	}

	/**
	 * Get outstanding invoice_queue
	 *
	 * @access public
	 * @return array $invoice_queue
	 */
	public function get_outstanding_invoice_queue() {
		return Invoice_Queue::get_unprocessed_by_customer_contact($this);
	}

	/**
	 * Get display_name
	 *
	 * @access public
	 * @return string $display_name
	 */
	public function get_display_name() {
		$display_name = '';
		if (!empty($this->details['company'])) {
			$display_name .= $this->details['company'];

			if (!empty($this->details['firstname']) or !empty($this->details['lastname'])) {
				$display_name .= ' (' . $this->details['firstname'] . ' ' . $this->details['lastname'] . ')';
			}

			return $display_name;
		}

		return $this->details['firstname'] . ' ' . $this->details['lastname'];
	}

	/**
	 * Get customer indentifier
	 *
	 * @access public
	 * @return string $identifier
	 */
	public function get_identifier() {
		try {
			$setting = Setting::get_by_name('customer_contact_identifier')->value;
		} catch (Exception $e) {
			$setting = '%d';
		}
		return sprintf($setting, $this->id);
	}

	/**
	 * Export
	 *
	 * @access private
	 */
	private function export() {
		if ($this->customer_contact_export_id == 0) {
			if ($this->vat != '') {
				try {
					$customer_contact_export = Customer_Contact_Export::get_by_country_vat($this->country, $this->vat);
				} catch (Exception $e) {
					$customer_contact_export = new Customer_Contact_Export();
				}
			} else {
				try {
					$customer_contact_export = Customer_Contact_Export::search('', $this->street, $this->housenumber, $this->zipcode, $this->city);
				} catch (Exception $e) {
					$customer_contact_export = new Customer_Contact_Export();
				}
			}
		} else {
			$customer_contact_export = Customer_Contact_Export::get_by_id($this->customer_contact_export_id);
		}

		$info = $this->get_info();
		unset($info['id']);
		$customer_contact_export->load_array($info);
		$customer_contact_export->save();

		if (empty($this->customer_contact_export_id) or $this->customer_contact_export_id != $customer_contact_export->id) {
			$this->customer_contact_export_id = $customer_contact_export->id;
			$this->save(false);
		}
	}

	/**
	 * Get active by Customer
	 *
	 * @access public
	 * @param Customer $customer
	 * @return array Customer_Contact $items
	 */
	public static function get_active_by_customer(Customer $customer) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE customer_id = ? AND active = 1 ORDER BY created DESC', [ $customer->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get by Customer
	 *
	 * @access public
	 * @param Customer $customer
	 * @return array Customer_Contact $items
	 */
	public static function get_by_customer(Customer $customer) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE customer_id = ? ORDER BY created DESC', [ $customer->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get by uuid
	 *
	 * @access public
	 * @param string $uuid
	 * @return Customer_Contact $customer_contact
	 */
	public static function get_by_uuid($uuid) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM customer_contact WHERE uuid=?', [ $uuid ]);
		return self::get_by_id($id);
	}
}
