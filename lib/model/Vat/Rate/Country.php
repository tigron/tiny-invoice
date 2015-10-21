<?php
/**
 * Vat_Rate_Country class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Vat_Rate_Country {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * Get by Country
	 *
	 * @access public
	 * @param Country $country
	 * @return array Vat_Rate_Country $items
	 */
	public static function get_by_country(Country $country) {
		$table = self::trait_get_database_table();
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE country_id=?', [ $country->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get by Vat_Rate Country
	 *
	 * @access public
	 * @param Vat_Rate $vat_rate
	 * @param Country $country
	 */
	public static function get_by_vat_rate_country(Vat_Rate $vat_rate, Country $country) {
		$table = self::trait_get_database_table();
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM ' . $table . ' WHERE vat_rate_id=? AND country_id=?', [ $vat_rate->id, $country->id ]);

		if ($id === null) {
			throw new Exception('No Vat_Rate_Country found');
		}

		return self::get_by_id($id);
	}
}
