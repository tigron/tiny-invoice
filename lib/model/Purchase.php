<?php
/**
 * Purchase class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Purchase {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Validate user data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = []) {
		$required_fields = [ 'supplier_id', 'date', 'expiration_date', 'price_incl', 'price_excl' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get purchase by document
	 *
	 * @access public
	 * @param Document $document
	 * @return Purchase
	 */
	public static function get_by_document(Document $document) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$id = $db->get_one('SELECT id FROM ' . $table . ' WHERE document_id = ?', [ $document->id ]);
		if ($id === null) {
			throw new Exception('Unknown Purchase');
		}

		return self::get_by_id($id);

	}

}
