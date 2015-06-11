<?php
/**
 * Vat_Rate_Country class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @version $Id$
 */

class Vat_Rate_Country {
	use Model, Get, Save, Delete;

	/**
	 * Get by Country
	 *
	 * @access public
	 * @param Country $country
	 * @return array Vat_Rate_Country $items
	 */
	public static function get_by_country(Country $country) {
		$table = self::trait_get_database_table();
		$db = Database::Get();
		$ids = $db->getCol('SELECT id FROM ' . $table . ' WHERE country_id=?', [ $country->id ]);

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
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM vat_rate_country WHERE vat_rate_id=? AND country_id=?', [ $vat_rate->id, $country->id ]);

		if ($id === null) {
			throw new Exception('No Vat_Rate_Country found');
		}

		return self::get_by_id($id);
	}
}
