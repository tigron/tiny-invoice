<?php
/**
 * Web Module User
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_User extends Module {
	/**
	 * Login required
	 *
	 * @access protected
	 * @var bool $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'user.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();

		$pager = new Pager('user');

		$pager->add_sort_permission('username');
		$pager->add_sort_permission('firstname');
		$pager->add_sort_permission('lastname');

		if (isset($_POST['search'])) {
			$pager->set_condition('%search%', $_POST['search']);
		}
		$pager->page();

		$template = Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Add
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Template::Get();

		if (isset($_POST['user'])) {
			$user = new User();
			$user->set_password($_POST['user']['password']);
			unset($_POST['user']['password']);
			$user->load_array($_POST['user']);
			if ($user->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$user->save();

				Session::set_sticky('message', 'created');
				Session::redirect('/user');
			}
		}

		$template->assign('languages', Language::get_all());
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::Get();
		$user = User::get_by_id($_GET['id']);

		if (isset($_POST['user'])) {
			if ($_POST['user']['password'] != 'DONOTUPDATEME') {
				$user->set_password($_POST['user']['password']);
			}
			unset($_POST['user']['password']);
			$user->load_array($_POST['user']);
			if ($user->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$user->save();

				Session::set_sticky('message', 'updated');
				Session::redirect('/user?action=edit&id=' . $user->id);
			}
		}

		$template->assign('user', $user);
		$template->assign('languages', Language::get_all());
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function display_delete() {
		if ($_SESSION['user']->admin != 1) {
			Session::redirect('/user');
		}

		try {
			$user = User::get_by_id($_GET['id']);
			if ($user->id == $_SESSION['user']->id) {
				throw new Exception('Not allowed to delete your own');
			}

			$user->delete();
			Session::set_sticky('message', 'deleted');
		} catch (Exception $e) {
			Session::set_sticky('message', 'error_delete');
		}

		Session::redirect('/user');
	}
}
