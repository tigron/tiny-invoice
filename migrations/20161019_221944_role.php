<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161019_221944_Role extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE `role` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `name` varchar(64) NOT NULL
			) ENGINE='InnoDB';
		", []);

		$db->query("
			CREATE TABLE `permission` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `identifier` varchar(64) NOT NULL
			) ENGINE='InnoDB';
		", []);

		$db->query("
			CREATE TABLE `role_permission` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `role_id` int NOT NULL,
			  `permission_id` int NOT NULL
			) ENGINE='InnoDB';
		", []);

		$db->query("
			ALTER TABLE `user`
			ADD `role_id` int NOT NULL AFTER `password`;
		", []);

		$db->query("
			INSERT INTO `role` (`name`)
			VALUES ('Administrator');
		", []);

		$db->query("
			UPDATE user SET role_id=1
		", []);

		$db->query("
			INSERT INTO `permission` (`id`, `identifier`) VALUES
			(1,	'admin.user'),
			(2,	'admin.customer'),
			(3,	'admin.supplier'),
			(4,	'admin.export'),
			(5,	'admin.invoice'),
			(6,	'admin.creditnote'),
			(7,	'admin.invoice_queue'),
			(8,	'admin.invoice_queue_recurring'),
			(9,	'admin.product_type'),
			(10,'admin.document'),
			(11,'admin.setting');
		", []);

		$role = Role::get_by_id(1);
		$permissions = Permission::get_all();
		foreach ($permissions as $permission) {
			$role_permission = new Role_Permission();
			$role_permission->role_id = $role->id;
			$role_permission->permission_id = $permission->id;
			$role_permission->save();
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
