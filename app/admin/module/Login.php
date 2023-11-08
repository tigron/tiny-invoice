<?php
/**
 * Module Login
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module;

use \Skeleton\Application\Web\Module;
use \Skeleton\Application\Web\Template;
use \Skeleton\Core\Http\Session;

class Login extends Module {

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
		if (isset($_POST['username']) AND isset($_POST['password'])) {
			try {
				$user = \User::authenticate($_POST['username'], $_POST['password']);
				\User::set($user);
				\Log::create('User logged in', $user);
				$_SESSION['user'] = $user;

				if (isset($_SESSION['redirect_uri'])) {
					$redirect = $_SESSION['redirect_uri'];
					unset($_SESSION['redirect_uri']);
					Session::redirect($redirect);
				} else {
					Session::redirect('/');
				}
			} catch (\Exception $e) {
				Template::get()->assign('error', true);
			}
		}
	}

	/**
	 * Logout
	 *
	 * @access public
	 */
	public function display_logout() {
		Session::destroy();
		Session::redirect('/');
	}
}
