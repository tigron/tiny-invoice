<?php
/**
 * Vat_Rate_Country class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

<<<<<<< HEAD
use \Skeleton\Database\Database;
=======
use Skeleton\Database\Database;
>>>>>>> origin/master

class Vat_Rate_Country {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
<<<<<<< HEAD
=======
	use \Skeleton\Pager\Page;
>>>>>>> origin/master

	/**
	 * Get by Country
	 *
	 * @access public
	 * @param Country $country
	 * @return array Vat_Rate_Country $items
	 */
	public static function get_by_country(Country $country) {
		$table = self::trait_get_database_table();
<<<<<<< HEAD
		$db = Database::get();
=======
		$db = Database::Get();
>>>>>>> origin/master
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
<<<<<<< HEAD
		$table = self::trait_get_database_table();
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM ' . $table . ' WHERE vat_rate_id=? AND country_id=?', [ $vat_rate->id, $country->id ]);
=======
		$db = Database::Get();
		$id = $db->get_one('SELECT id FROM vat_rate_country WHERE vat_rate_id=? AND country_id=?', [ $vat_rate->id, $country->id ]);
>>>>>>> origin/master

		if ($id === null) {
			throw new Exception('No Vat_Rate_Country found');
		}

		return self::get_by_id($id);
	}
}
