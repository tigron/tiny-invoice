<?php
/**
 * Role module
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Role extends Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'administrative/role.twig';

	/**
	 * Display
	 * This is the default method for a module.
	 *
	 * @access public
	 */
	public function display() {
		$pager = new Pager('Role');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}

		$pager->add_sort_permission('name');
		$pager->page();

		$template = Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Display delete
	 * Deletes a user
	 *
	 * @access private
	 */
	protected function display_delete() {
		$role = Role::get_by_id($_GET['id']);
		$role->delete();

		Session::redirect('/administrative/role');
	}

	/**
	 * Display Add
	 * Adds a new user
	 *
	 * @access private
	 */
	protected function display_add() {
		$role = new Role();
		$role->load_array($_POST['role']);
		$role->save();

		Session::redirect('/administrative/role');
	}

	/**
	 * Display edit
	 * Display the detailed information of a user
	 *
	 * @access private
	 */
	public function display_edit() {
		$template = Template::get();
		$role = Role::get_by_id($_GET['id']);

		if (isset($_POST['role'])) {
			$role->load_array($_POST['role']);
			$role->save();

			Session::set_sticky('message', 'updated');
			Session::redirect('/administrative/role?action=edit&id=' . $role->id);
		}

		$permissions = Permission::get_all();
		$template->assign('permissions', $permissions);
		$template->assign('role', $role);
	}

	public function display_edit_permissions() {
		$role = Role::get_by_id($_GET['id']);
		if (empty($_POST['permission_ids'])) {
			$_POST['permission_ids'] = [];
		}
		$role_permissions = Role_Permission::get_by_role($role);
		foreach ($role_permissions as $role_permission) {
			$role_permission->delete();
		}
		foreach ($_POST['permission_ids'] as $permission_id) {
			$permission = Permission::get_by_id($permission_id);
			$role_permission = new Role_Permission();
			$role_permission->role_id = $role->id;
			$role_permission->permission_id = $permission->id;
			$role_permission->save();
		}
		Session::set_sticky('message', 'updated_permissions');
		Session::redirect('/administrative/role?action=edit&id=' . $role->id);
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.user';
	}
}
