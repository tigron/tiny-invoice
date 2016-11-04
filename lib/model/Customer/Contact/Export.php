<?php
/**
 * Customer_Contact_Export class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Customer_Contact_Export {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

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
	 * Search an Customer_Contact_Export
	 *
	 * @access public
	 * @param string $vat
	 * @param string $street
	 * @param string $housenumber
	 * @param string $zipcode
	 * @param string $city
	 * @return Customer_Contact_Export $customer_contact_export
	 */
	public static function search($vat, $street, $housenumber, $zipcode, $city) {
		$db = Database::Get();
		$id = $db->get_one('SELECT id FROM customer_contact_export WHERE vat=? AND street=? AND housenumber=? AND zipcode=? AND city=? LIMIT 1', [ $vat, $street, $housenumber, $zipcode, $city ]);
		if ($id === null) {
			throw new Exception('Customer_Contact_Export not found');
		}
		return self::get_by_id($id);
	}

	/**
	 * Get by country vat
	 *
	 * @access public
	 * @param Country $country
	 * @param string $vat
	 * @return Customer_Contact_Export $customer_contact_export
	 */
	public static function get_by_country_vat(Country $country, $vat) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM customer_contact_export WHERE country_id=? AND vat=?', [ $country->id, $vat ]);
		if ($id === null) {
			throw new Exception('Customer_Contact_Export not found');
		}
		return self::get_by_id($id);
	}

}
