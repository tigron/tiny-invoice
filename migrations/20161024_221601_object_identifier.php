<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161024_221601_Object_identifier extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		try {
			$setting = Setting::get_by_name('customer_identifier');
		} catch (Exception $e) {
			$setting = new Setting();
			$setting->name = 'customer_identifier';
			$setting->value = '%d';
			$setting->save();
		}

		try {
			$setting = Setting::get_by_name('customer_contact_identifier');
		} catch (Exception $e) {
			$setting = new Setting();
			$setting->name = 'customer_contact_identifier';
			$setting->value = '%d';
			$setting->save();
		}

	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
