<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20170602_120343_Canary_islands extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$spain_id = $db->get_one('SELECT id FROM country WHERE name="Spain"', []);
		$canary_islands_id = $db->get_one('SELECT id FROM country WHERE name="Canary Islands"', []);

		$db->query("UPDATE customer SET country_id=? WHERE country_id=?", [ $spain_id, $canary_islands_id ]);
		$db->query("UPDATE customer_contact SET country_id=? WHERE country_id=?", [ $spain_id, $canary_islands_id ]);
		$db->query("UPDATE customer_contact_export SET country_id=? WHERE country_id=?", [ $spain_id, $canary_islands_id ]);
		$db->query("UPDATE supplier SET country_id=? WHERE country_id=?", [ $spain_id, $canary_islands_id ]);
		$db->query("UPDATE vat_check_cache SET country_id=? WHERE country_id=?", [ $spain_id, $canary_islands_id ]);
		$db->query("DELETE FROM vat_rate_country WHERE country_id=?", [ $canary_islands_id ]);

		$db->query('DELETE FROM country WHERE id=?', [ $canary_islands_id ]);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
