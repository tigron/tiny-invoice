<?php
/**
 * Role_Permission class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Role_Permission {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Get by Role
	 *
	 * @access public
	 * @param Role $role
	 * @return array $role_permissions
	 */
	public static function get_by_role(Role $role) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM role_permission WHERE role_id=?', [ $role->id ]);
		$role_permissions = [];
		foreach ($ids as $id) {
			$role_permissions[] = self::get_by_id($id);
		}
		return $role_permissions;
	}

}
