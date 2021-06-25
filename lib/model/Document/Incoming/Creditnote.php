<?php
/**
 * Document_Incoming_Creditnote class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Document_Incoming_Creditnote extends Document {
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
		$db->query('DELETE FROM document_incoming_creditnote WHERE document_id=?', [ $this->id ]);
		return parent::change_classname($classname);
	}

	/**
	 * Validate document data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = null) {
		$errors = [];
		parent::validate($parent_errors);

		$required_fields = [ 'supplier_id', 'expiration_date', 'price_incl', 'price_excl' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->$required_field) OR $this->$required_field == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (isset($this->price_incl) and !is_numeric($this->price_incl)) {
			$errors['price_incl'] = 'incorrect';
		}

		if (isset($this->price_excl) and !is_numeric($this->price_excl)) {
			$errors['price_excl'] = 'incorrect';
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
		$info['supplier_id'] = $this->supplier->uuid;
		return $info;
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
			SELECT document.uuid
			FROM document_incoming_creditnote, document
			WHERE
			document_incoming_creditnote.document_id = document.id
			AND 1';


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
