<?php
/**
 * Module Login
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_Login extends Web_Module {

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
		$template = Web_Template::Get();

		if (isset($_POST['username']) AND isset($_POST['password'])) {
			try {
				$user = User::authenticate($_POST['username'], $_POST['password']);
				Object_Log::create('User logged in', $user);
				$_SESSION['user'] = $user;
				Web_Session::Redirect('/');
			} catch (Exception $e) {
				$template->assign('error', true);
			}
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