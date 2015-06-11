<?php
/**
 * Web Module User
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_User extends Web_Module {
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
		$template = Web_Template::Get();

		$pager = new Web_Pager('user');
		$permissions = array(
			'username' => 'username',
			'firstname' => 'firstname',
			'lastname' => 'lastname'
		);
		$pager->set_sort_permissions($permissions);

		if (isset($_POST['search'])) {
			$pager->set_condition('%search%', $_POST['search']);
		}
		$pager->page();

		$template = Web_Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Add
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Web_Template::Get();

		if (isset($_POST['user'])) {
			$user = new User();
			$user->load_array($_POST['user']);
			if ($user->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$user->save();

				$session = Web_Session_Sticky::Get();
				$session->message = 'created';
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
		$template = Web_Template::Get();
		$user = User::get_by_id($_GET['id']);

		if (isset($_POST['user'])) {
			$user->load_array($_POST['user']);
			$user->save();

			$session = Web_Session_Sticky::Get();
			$session->message = 'updated';
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

		$session = Web_Session_Sticky::Get();

		try {
			$user = User::get_by_id($_GET['id']);
			if ($user->id == $_SESSION['user']->id) {
				throw new Exception('Not allowed to delete your own');
			}

			$user->delete();
			$message = 'deleted';
		} catch (Exception $e) {
			$message = 'error_delete';
		}

		$session->message = $message;
		Web_Session::Redirect('/user');
	}
}
