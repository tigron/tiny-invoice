<?php
/**
 * Document_Incoming_Invoice class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Document_Incoming_Invoice extends Document {

	/**
	 * Local details
	 *
	 * @access private
	 * @var array $local_details
	 */
	private $local_details = [];

	/**
	 * Get details
	 *
	 * @access protected
	 */
	protected function get_details() {
		parent::get_details();
		$db = Database::Get();

		$local_details = $db->get_row('SELECT * FROM document_incoming_invoice WHERE document_id=?', [ $this->id ]);
		if ($local_details === null) {
			$this->local_details = array();
		}

		$this->local_details = $local_details;
	}

	/**
	 * Change classname
	 *
	 * @access public
	 * @param string $classname
	 */
	public function change_classname($classname) {
		if ($classname == $this->classname) {
			return $this;
		}
		$db = Database::get();
		$db->query('DELETE FROM document_incoming_invoice WHERE document_id=?', [ $this->id ]);
		return parent::change_classname($classname);
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$db = Database::get();
		$db->query('DELETE FROM document_incoming_invoice WHERE document_id=?', [ $this->id ]);
		parent::delete();
	}

	/**
	 * Validate document data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors) {
		$errors = [];
		parent::validate($parent_errors);

		$required_fields = [ 'supplier_id', 'expiration_date', 'price_incl', 'price_excl' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->local_details[$required_field]) OR $this->local_details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (!empty($this->local_details['payment_structured_message'])) {
			$parts = explode('/', $this->local_details['payment_structured_message']);
			if (count($parts) != 3) {
				$errors['payment_structured_message'] = 'incorrect';
			} elseif (strlen($parts[0]) != 3 OR strlen($parts[1]) != 4 OR strlen($parts[2]) != 5) {
				$errors['payment_structured_message'] = 'incorrect';
			} else {
				$number = $this->local_details['payment_structured_message'];
				$number = str_replace('/', '', $number);
				$modulus = substr($number, -2);
				$number = substr($number, 0, 10);
				if ($number % 97 != $modulus) {
					$errors['payment_structured_message'] = 'incorrect';
				}
			}
		}

		if (!empty($this->details['supplier_id']) and !empty($this->details['supplier_identifier']) and !empty($this->details['price_excl'])) {
			$supplier = Supplier::get_by_id($this->details['supplier_id']);
			$invoices = self::get_by_supplier_supplier_identifier($supplier, $this->details['supplier_identifier']);
			foreach ($invoices as $key => $invoice) {
				if ($invoice->id == $this->id) {
					unset($invoices[$key]);
				}
				if ($invoice->price_excl != $this->price_excl) {
					unset($invoices[$key]);
				}
			}
			if (count($invoices) > 0) {
				$errors['supplier_identifier'] = 'unique';
			}
		}

		if (!empty($this->details['accounting_identifier'])) {
			$invoices = self::get_by_accounting_identifier($this->details['accounting_identifier']);
			foreach ($invoices as $key => $invoice) {
				if ($invoice->id == $this->id) {
					unset($invoices[$key]);
				}
			}
			if (count($invoices) > 0) {
				$errors['accounting_identifier'] = 'unique';
			}
		}

		if (isset($this->local_details['price_incl']) AND isset($this->local_details['price_excl'])) {
			if ($this->local_details['price_incl'] < $this->local_details['price_excl']) {
				$errors['price_incl'] = 'incorrect';
			}
		}

		$errors = array_merge($errors, $parent_errors);

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get
	 *
	 * @access public
	 * @param string $key
	 */
	public function __get($key) {
		if ($key == 'supplier') {
			return Supplier::get_by_id($this->supplier_id);
		} elseif (isset($this->local_details[$key])) {
			return $this->local_details[$key];
		} else {
			return parent::__get($key);
		}
	}

	/**
	 * Set
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value) {
		$this->local_details[$key] = $value;
		parent::__set($key, $value);
	}

	/**
	 * Isset
	 *
	 * @access public
	 * @param string $key
	 * @return bool $isset
	 */
	public function __isset($key) {
		if ($key == 'supplier') {
			return true;
		} elseif (isset($this->local_details[$key])) {
			return true;
		} else {
			return parent::__isset($key);
		}
	}

	/**
	 * Get info
	 *
	 * @access public
	 * @return array $info
	 */
	public function get_info() {
		$parent_info = parent::get_info();
		$info = $this->local_details;
		unset($info['id']);
		unset($info['document_id']);
		return array_merge($info, $parent_info);
	}

	/**
	 * Save / Create user
	 *
	 * @access public
	 */
	public function save() {
		if (!isset($this->id)) {
			parent::save();
		}
		$this->local_details['document_id'] = $this->id;

		$db = Database::Get();
		$count = $db->get_one('SELECT count(*) FROM document_incoming_invoice WHERE document_id=?', [ $this->id ]);
		if ($count == 0) {
			$db->insert('document_incoming_invoice', $this->local_details);
		} else {
			$where = 'document_id = ' . $db->quote($this->id);
			$db->update('document_incoming_invoice', $this->local_details, $where);
		}

		parent::save();
	}

	/**
	 * Get all ids
	 *
	 * @access public
	 * @return array $ids
	 */
	public function get_all_ids() {
		$db = Database::get();

		$public = false;
		try {
			$public = Setting::get_by_name('api_public_documents')->value;
			if ($public) {
				$public = true;
			}
		} catch (Exception $e) { };

		$sql = '
			SELECT document_id
			FROM document_incoming_invoice
			WHERE
			1';


		if (!$public) {
			try {
				$tag_ids = Setting::get_by_name('api_document_tag_ids')->value;
			} catch (Exception $e) {
				$tag_ids = '0';
			}
			if (trim($tag_ids) == '') {
				$tag_ids = '0';
			}

			$sql .= '
			AND document_id IN (SELECT document_id FROM document_tag WHERE tag_id IN ( ' . $tag_ids . '))';
		}

		$ids = $db->get_column($sql, []);
		return $ids;
	}

	/**
	 * Get expiring incoming invoices
	 *
	 * @access public
	 * @param  string $days_to_expire
	 * @return array Purchases
	 */
	public static function get_expiring($days_to_expire = '+7 days') {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_incoming_invoice WHERE paid = 0 AND DATE(expiration_date) <= ?', [ strtotime($days_to_expire) ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get by Supplier supplier_identifier
	 *
	 * @access public
	 * @param Supplier $supplier
	 * @param string $supplier_identifier
	 */
	public static function get_by_supplier_supplier_identifier(Supplier $supplier, $supplier_identifier) {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_incoming_invoice WHERE supplier_id=? AND supplier_identifier=?', [ $supplier->id, $supplier_identifier]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get by accounting_identifier
	 *
	 * @access public
	 * @param string $accounting_identifier
	 */
	public static function get_by_accounting_identifier($accounting_identifier) {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_incoming_invoice WHERE accounting_identifier=?', [ $accounting_identifier]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}
}
