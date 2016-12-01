<?php
/**
 * Permission class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Permission {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Get by identifier
	 *
	 * @access public
	 * @param string $identifier
	 * @return Permission $permission
	 */
	public static function get_by_identifier($identifier) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM permission WHERE identifier=?', [ $identifier ]);
		if ($id === null) {
			throw new Exception('Permission not found');
		}
		return self::get_by_id($id);
	}

}
