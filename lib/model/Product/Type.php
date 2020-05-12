<?php
/**
 * Product_Type class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Product_Type {
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
	public function validate(&$errors = null) {
		$errors = [];
		$required_fields = [ 'name' ];
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
	 * Get by identifier
	 *
	 * @access public
	 * @param string $identifier
	 * @return Product_Type $product_type
	 */
	public static function get_by_identifier($identifier) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM product_type WHERE identifier=?', [ $identifier ]);
		if ($id === null) {
			throw new Exception('No product type found');
		}
		return self::get_by_id($id);
	}

}
