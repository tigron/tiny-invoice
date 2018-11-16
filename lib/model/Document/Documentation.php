<?php
/**
 * Document_Documentation class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Document_Documentation extends Document {
	use \Skeleton\Object\Child;

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
		$db->query('DELETE FROM document_documentation WHERE document_id=?', [ $this->id ]);
		return parent::change_classname($classname);
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
			if (!isset($this->$required_field) OR $this->$required_field == '') {
				$errors[$required_field] = 'required';
			}
		}

		if ($this->customer_id == 0 and $this->supplier_id == 0) {
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
	 * Get info
	 *
	 * @access public
	 * @return array $info
	 */
	public function get_info() {
		$info = parent::get_info();
		unset($info['id']);
		unset($info['document_id']);
		return $info;
	}

	/**
	 * Get by customer
	 *
	 * @access public
	 * @param Customer $customer
	 * @return array $document_documentations
	 */
	public static function get_by_customer(Customer $customer) {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_documentation WHERE customer_id=?', [ $customer->id ]);
		$documentations = [];
		foreach ($ids as $id) {
			$documentations[] = self::get_by_id($id);
		}
		return $documentations;
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
		$ids = $db->get_column('SELECT document_id FROM document_documentation WHERE supplier_id=?', [ $supplier->id ]);
		$documentations = [];
		foreach ($ids as $id) {
			$documentations[] = self::get_by_id($id);
		}
		return $documentations;
	}
}
