<?php
/**
 * Vat_Rate class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 */

use \Skeleton\Database\Database;

class Vat_Rate {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * get by name
	 *
	 * @access public
	 * @param string $name
	 * @return Vat_Rate
	 */
	public static function get_by_name(string $name): Vat_Rate {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM vat_rate WHERE name = ?', [ $name ]);
		if ($id === null) {
			throw new Exception('No vat_rate found with name ' . $name);
		}
		return self::get_by_id($id);
	}
}
