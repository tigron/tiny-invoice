<?php
/**
 * Country class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Country {
	use Get, Delete, Model, Save;

	/**
	 * Get by ISO2
	 *
	 * @access public
	 * @param string $iso2
	 * @return Country $country
	 */
	public static function get_by_iso2($iso2) {
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM country WHERE ISO2=?', array($iso2));

		if ($id == null) {
			throw new Exception('No such country');
		} else {
			return Country::get_by_id($id);
		}
	}

	/**
	 * Get grouped
	 *
	 * @access public
	 * @return array $countries
	 */
	public static function get_grouped() {
		$db = Database::Get();
		$db_countries = $db->getAll('SELECT * FROM country WHERE european=1 ORDER BY name ASC', array());

		$countries = array(	'european' => array(), 'rest' => array() );
		foreach ($db_countries as $db_country) {
			$country = new Country();
			$country->id = $db_country['id'];
			$country->details = $db_country;
			$countries['european'][] = $country;
		}

		$db_countries = $db->getAll('SELECT * FROM country WHERE european=0 ORDER BY name ASC', array());
		foreach ($db_countries as $db_country) {
			$country = new Country();
			$country->id = $db_country['id'];
			$country->details = $db_country;
			$countries['rest'][] = $country;
		}
		return $countries;
	}
}
