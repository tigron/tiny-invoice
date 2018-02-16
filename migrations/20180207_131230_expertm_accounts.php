<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @author Lionel Laffineur <lionel@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20180207_131230_Expertm_accounts extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();


		try {
			$setting = Setting::get_by_name('expertm.centralization_account_customer');
		} catch (Exception $e) {
			$setting = new Setting();
			$setting->name = 'expertm.centralization_account_customer';
		}
		$setting->value = '4000000';
		$setting->save();

		try {
			$setting = Setting::get_by_name('expertm.centralization_account_sale');
		} catch (Exception $e) {
			$setting = new Setting();
			$setting->name = 'expertm.centralization_account_sale';
		}
		$setting->value = '7000000';
		$setting->save();
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
