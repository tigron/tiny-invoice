<?php
/**
 * Web Module User
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Administrative\User;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Application\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Detail extends Module {
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
	protected $template = 'administrative/user/detail.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();
		$user = \User::get_by_id($_GET['id']);

		if (isset($_POST['user'])) {
			if ($_POST['user']['password'] != 'DONOTUPDATEME') {
				$user->set_password($_POST['user']['password']);
			}
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

				Session::set_sticky('message', 'updated');
				Session::redirect('/administrative/user/detail?id=' . $user->id);
			}
		}
		$template->assign('roles', \Role::get_all());
		$template->assign('user', $user);
		$template->assign('languages', \Language::get_all());
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
