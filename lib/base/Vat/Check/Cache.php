<?php
/**
 * Vat_Check_Cache class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Vat_Check_Cache {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Delete;
	use \Skeleton\Object\Save;

	/**
	 * Returns a Vat_Check_Cache by supplying a VAT number
	 *
	 * @access public
	 * @param string VAT number
	 * @return Vat_Check_Cache $vat_check_cache
	 */
	public static function get_by_number_country($number, Country $country) {
		$table = self::trait_get_database_table();
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM ' . $table . ' WHERE number = ? AND country_id = ?', [ $number, $country->id] );
		if ($id === NULL) {
			throw new Exception('Unknown VAT');
		}

		return self::get_by_id($id);
	}

	/**
	 * Get all Vat_Checks_Cache that are expired
	 *
	 * @access public
	 * @return array Vat_Checks_Cache $items
	 */
	public static function get_overdue() {
		$table = self::trait_get_database_table();
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE created < DATE_SUB(NOW(), INTERVAL 2 DAY)');
		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}
		return $items;
	}
}