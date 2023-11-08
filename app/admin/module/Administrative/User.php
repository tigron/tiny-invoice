<?php
/**
 * Web Module User
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Administrative;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;
use \Skeleton\Pager\Web\Pager;

class User extends Module {
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
	protected $template = 'administrative/user.twig';

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
		$pager->add_sort_permission('role.name');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
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
			$user = new \User();
			$user->set_password($_POST['user']['password']);
			unset($_POST['user']['password']);
			if (isset($_POST['user']['receive_expired_invoice_overview'])) {
				$_POST['user']['receive_expired_invoice_overview'] = 1;
			} else {
				$_POST['user']['receive_expired_invoice_overview'] = 0;
			}
			$user->load_array($_POST['user']);
			if ($user->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$user->save();

				Session::set_sticky('message', 'created');
				Session::redirect('/administrative/user');
			}
		}
		$template->assign('roles', \Role::get_all());
		$template->assign('languages', \Language::get_all());
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function display_delete() {
		try {
			$user = \User::get_by_id($_GET['id']);
			if ($user->id == $_SESSION['user']->id) {
				throw new \Exception('Not allowed to delete your own');
			}

			$user->delete();
			Session::set_sticky('message', 'deleted');
		} catch (\Exception $e) {
			Session::set_sticky('message', 'error_delete');
		}

		Session::redirect('/administrative/user');
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
