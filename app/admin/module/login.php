<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/model/User.php';

class Module_Login extends Web_Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = false;

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = 'login.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
	}

	/**
	 * Login
	 *
	 * @access public
	 */
	public function display_login() {
		$template = Web_Template::Get();
		try {
			$user = User::authenticate($_POST['username'], $_POST['password']);
			$_SESSION['user'] = $user;
			Web_Session::Redirect('/');
		} catch (Exception $e) {
			$template->assign('error', true);
		}
	}

	/**
	 * Logout
	 *
	 * @access public
	 */
	public function display_logout() {
		Web_Session::Destroy();
		Web_Session::Redirect('/');
	}
}
