<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20191121_120314_Country_i18n extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$countries = Country::get_all();
		$languages = Language::get_all();
		foreach ($countries as $country) {
			foreach ($languages as $language) {
				$required_field = 'text_' . $language->name_short . '_name';
				$country->$required_field = \Punic\Territory::getName($country->get_iso2(), $language->name_short);
			}
			$country->save();
		}
		$db->query("ALTER TABLE `country` DROP COLUMN `name`");
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
