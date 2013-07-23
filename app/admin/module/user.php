<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/model/User.php';

class Module_User extends Web_Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = true;

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = 'user.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		if (isset($_POST['user'])) {
			$user = new User();
			$user->load_array($_POST['user']);
			$user->set_password($_POST['user']['password']);
			$user->save();
			Web_Session::Redirect('/user');
		}

		$template = Web_Template::Get();
		$users = User::get_paged();
		$template->assign('users', $users);
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Web_Template::Get();
		$user = User::get_by_id($_GET['id']);

		if (isset($_POST['user'])) {
			$user->load_array($_POST['user']);

			if (isset($_POST['user']['password'])) {
				$user->set_password($_POST['user']['password']);
				unset($_POST['user']['password']);
			}

			$user->save();
			$template->assign('saved', true);
		}
		$template->assign('user', $user);
	}
}
