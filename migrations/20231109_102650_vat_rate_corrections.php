<?php
/**
 * Database migration class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */


use \Skeleton\Database\Database;

class Migration_20231109_102650_Vat_rate_corrections extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up(): void {
		$db = Database::get();
		$vat_changes = [
			[ 'country_code' => 'ES', 'old_vat' => 12, 'new_vat' => 10, 'vat_rate' => 'Reduced A', 'id' => 24 ],
			[ 'country_code' => 'LV', 'old_vat' => null, 'new_vat' => 5, 'vat_rate' => 'Reduced B', 'id' => null ],
			[ 'country_code' => 'NL', 'old_vat' => 6, 'new_vat' => 9, 'vat_rate' => 'Reduced A', 'id' => 56 ],
			[ 'country_code' => 'NL', 'old_vat' => 12, 'new_vat' => 'DELETE', 'vat_rate' => 'DELETE', 'id' => 83 ],
			[ 'country_code' => 'SI', 'old_vat' => null, 'new_vat' => 5, 'vat_rate' => 'Reduced B', 'id' => null ],
		];
		foreach ($vat_changes as $vat_change) {
			if (empty($vat_change['id'])) {
				$vat_rate_country = new Vat_Rate_Country();
				$vat_rate_country->vat_rate_id = Vat_Rate::get_by_name($vat_change['vat_rate'])->id;
				$vat_rate_country->country_id = Country::get_by_vat($vat_change['country_code'])->id;
				$vat_rate_country->vat = $vat_change['new_vat'];
				$vat_rate_country->save();
			} elseif ($vat_change['vat_rate'] === 'DELETE') {
				$vat_rate_country = Vat_Rate_Country::get_by_id($vat_change['id']);
				$vat_rate_country->delete();
			} else {
				$vat_rate_country = Vat_Rate_Country::get_by_id($vat_change['id']);
				$vat_rate_country->vat = $vat_change['new_vat'];
				$vat_rate_country->save();
			}
		}
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down(): void {

	}
}
