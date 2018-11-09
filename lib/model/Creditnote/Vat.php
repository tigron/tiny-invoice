<?php
/**
 * Creditnote_Vat class
 */

use \Skeleton\Database\Database;

class Creditnote_Vat {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * Get by Creditnote and Vat_Rate
	 *
	 * @access public
	 * @param Creditnote
	 * @param Vat_Rate
	 * @return array Creditnote_Vat $creditnote_vats
	 */
	public static function get_by_creditnote_vat_rate(Creditnote $creditnote, Vat_Rate $vat_rate) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE creditnote_id = ? AND vat_rate_id = ?', [ $creditnote->id, $vat_rate->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get by Creditnote
	 *
	 * @access public
	 * @param Creditnote
	 * @return array Creditnote_Vat $creditnote_vats
	 */
	public static function get_by_creditnote(Creditnote $creditnote) {
		$db = Database::get();
		$table = self::trait_get_database_table();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE creditnote_id = ?', [ $creditnote->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}

		return $items;
	}
}
