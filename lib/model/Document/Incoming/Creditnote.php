<?php
/**
 * Document_Incoming_Creditnote class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Document_Incoming_Creditnote extends Document {

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

		$local_details = $db->get_row('SELECT * FROM document_incoming_creditnote WHERE document_id=?', [ $this->id ]);
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
		$db->query('DELETE FROM document_incoming_creditnote WHERE document_id=?', [ $this->id ]);
		return parent::change_classname($classname);
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$db = Database::get();
		$db->query('DELETE FROM document_incoming_creditnote WHERE document_id=?', [ $this->id ]);
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
			if (!isset($this->local_details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
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
	 * Get transfer amount
	 *
	 * @access public
	 * @return double $amount
	 */
	public function get_transaction_amount() {
		$amount = 0;
		foreach ($this->get_bank_account_statement_transaction_balances() as $transaction) {
			$amount += $transaction->amount;
		}
		return $amount;
	}

	/**
	 * Get bank account statement transaction balances
	 *
	 * @access public
	 * @return array $balances
	 */
	public function get_bank_account_statement_transaction_balances() {
		return Bank_Account_Statement_Transaction_Balance::get_by_linked_object($this);
	}

	/**
	 * Get balance
	 *
	 * @access public
	 */
	public function get_balance() {
		return $this->price_incl - $this->get_transaction_amount();
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
		$count = $db->get_one('SELECT count(*) FROM document_incoming_creditnote WHERE document_id=?', [ $this->id ]);
		if ($count == 0) {
			$db->insert('document_incoming_creditnote', $this->local_details);
		} else {
			$where = 'document_id = ' . $db->quote($this->id);
			$db->update('document_incoming_creditnote', $this->local_details, $where);
		}

		parent::save($validate);
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
			FROM document_incoming_creditnote
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
	 * Get by accounting_identifier
	 *
	 * @access public
	 * @param string $accounting_identifier
	 */
	public static function get_by_accounting_identifier($accounting_identifier) {
		$db = Database::get();
		$ids = $db->get_column('SELECT document_id FROM document_incoming_creditnote WHERE accounting_identifier=?', [ $accounting_identifier]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}
}
