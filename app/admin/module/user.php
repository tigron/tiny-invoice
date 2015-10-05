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
			$user->load_array($_POST['user']);
			if ($user->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$user->save();

				Session::set_sticky('message', 'created');
				Web_Session::Redirect('/user');
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
			$user->load_array($_POST['user']);
			$user->save();

			Session::set_sticky('message', 'updated');
			Web_Session::Redirect('/user?action=edit&id=' . $user->id);
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
			Web_Session::Redirect('/user');
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

		$session->message = $message;
		Session::Redirect('/user');
	}
}
