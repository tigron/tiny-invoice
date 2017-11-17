<?php
/**
 * Document_Contract class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Document_Contract extends Document {

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

		$local_details = $db->get_row('SELECT * FROM document_contract WHERE document_id=?', [ $this->id ]);
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
		$db->query('DELETE FROM document_contract WHERE document_id=?', [ $this->id ]);
		return parent::change_classname($classname);
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$db = Database::get();
		$db->query('DELETE FROM document_contract WHERE document_id=?', [ $this->id ]);
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


		$required_fields = [  ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->local_details[$required_field]) OR $this->local_details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if ($this->local_details['customer_id'] == 0 and $this->local_details['supplier_id'] == 0) {
			$errors['customer_id'] = true;
			$errors['supplier_id'] = true;
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
		} elseif ($key == 'customer') {
			return Customer::get_by_id($this->customer_id);
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
		} elseif ($key == 'customer') {
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
	public function save($validate = true) {
		if (!isset($this->id)) {
			parent::save($validate);
		}
		$this->local_details['document_id'] = $this->id;

		$db = Database::Get();
		$count = $db->get_one('SELECT count(*) FROM document_contract WHERE document_id=?', [ $this->id ]);
		if ($count == 0) {
			$db->insert('document_contract', $this->local_details);
		} else {
			$where = 'document_id = ' . $db->quote($this->id);
			$db->update('document_contract', $this->local_details, $where);
		}

		parent::save($validate);
	}

	/**
	 * Get by customer
	 *
	 * @access public
	 * @param Customer $customer
	 * @return array $document_contracts
	 */
	public static function get_by_customer(Customer $customer) {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_contract WHERE customer_id=?', [ $customer->id ]);
		$contracts = [];
		foreach ($ids as $id) {
			$contracts[] = self::get_by_id($id);
		}
		return $contracts;
	}

	/**
	 * Get by supplier
	 *
	 * @access public
	 * @param Supplier $supplier
	 * @return array $document_contracts
	 */
	public static function get_by_supplier(Supplier $supplier) {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_contract WHERE supplier_id=?', [ $supplier->id ]);
		$contracts = [];
		foreach ($ids as $id) {
			$contracts[] = self::get_by_id($id);
		}
		return $contracts;
	}
}
