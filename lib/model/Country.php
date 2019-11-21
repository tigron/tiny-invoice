<?php
/**
 * Country class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Country {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get {
		get_info as trait_get_info;
	}
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * Text fields
	 *
	 * @access private
	 * @var array $text_fields
	 */
	protected static $object_text_fields = [ 'name' ];

	/**
	 * Get info
	 *
	 * @access public
	 * @return array $info
	 */
	public function get_info() {
		$info = $this->trait_get_info();

		$languages = Language::get_all();
		foreach ($languages as $language) {
			foreach (self::$object_text_fields as $field) {
				$name_key = 'text_' . $language->name_short . '_' . $field;
				$info[$name_key] = $this->$name_key;
			}
		}

		return $info;
	}

	/**
	 * Get the ISO2 code of the country
	 *
	 * @access public
	 * @return string $iso2
	 */
	public function get_iso2() {
		return $this->iso2;
	}

	/**
	 * Get by ISO2
	 *
	 * @access public
	 * @param string $iso2
	 * @return Country $country
	 */
	public static function get_by_iso2($iso2) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$id = $db->get_one('SELECT id FROM ' . $table . ' WHERE iso2 = ?', [ $iso2 ]);

		if ($id === null) {
			throw new Exception('No such country');
		}

		return self::get_by_id($id);
	}

	/**
	 * Get by VAT
	 *
	 * @access public
	 * @param string $vat
	 * @return Country $country
	 */
	public static function get_by_vat($vat) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$id = $db->get_one('SELECT id FROM ' . $table . ' WHERE vat = ?', [ $vat ]);

		if ($id === null) {
			throw new Exception('No such country');
		}

		return self::get_by_id($id);
	}

	/**
	 * Get grouped
	 *
	 * @access public
	 * @return array $countries
	 */
	public static function get_grouped() {
		$countries = [
			'european' => [],
			'rest' => []
		];
		$language = Language::get();

		$db = Database::get();
		$table = self::trait_get_database_table();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE european = 1', []);
		foreach ($ids as $id) {
			$countries['european'][] = self::get_by_id($id);
		}

		usort($countries['european'], function($a, $b) use ($language) {
			$key = 'text_' . $language->name_short . '_name';
			return strnatcmp($a->$key, $b->$key);
		});

		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE european = 0', []);
		foreach ($ids as $id) {
			$countries['rest'][] = self::get_by_id($id);
		}

		usort($countries['rest'], function($a, $b) use ($language) {
			$key = 'text_' . $language->name_short . '_name';
			return strnatcmp($a->$key, $b->$key);
		});

		return $countries;
	}
}
