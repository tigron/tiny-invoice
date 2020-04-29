<?php
/**
 * Tag class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Role {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete {
		delete as trait_delete;
	}
	use \Skeleton\Pager\Page;

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$document_tags = Document_Tag::get_by_tag($this);
		foreach ($document_tags as $document_tag) {
			$document_tag->delete();
		}

		$this->trait_delete();
	}

	/**
	 * Has permission
	 *
	 * @access public
	 * @param Permission $permission
	 */
	public function has_permission(Permission $permission) {
		$role_permissions = Role_Permission::get_by_role($this);
		foreach ($role_permissions as $role_permission) {
			if ($role_permission->permission_id == $permission->id) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get permissions
	 *
	 * @access public
	 */
	public function get_permissions() {
		$role_permissions = Role_Permission::get_by_role($this);
		$permissions = [];
		foreach ($role_permissions as $role_permission) {
			$permissions[] = $role_permission->permission;
		}
		return $permissions;
	}

	/**
	 * Validate a tag object
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = null) {
		$errors = [];
		$required_fields = [ 'name' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (count($errors) == 0) {
			return true;
		} else {
			return false;
		}
	}
}
