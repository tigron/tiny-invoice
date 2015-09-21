<?php
/**
 * Hooks
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 */

class Hook_Admin {
	/**
	 * Bootstrap the application
	 *
	 * @access private
	 */
	public static function bootstrap(\Skeleton\Core\Web\Module $module) {
		// Bootsprap the application
		// Example: check if we need to log in, if we do and we aren't, redirect
		if ($module->is_login_required()) {
			if (!isset($_SESSION['user'])) {
				\Skeleton\Core\Web\Session::destroy();
				\Skeleton\Core\Web\Session::redirect('/login');
			}

			User::set($_SESSION['user']);
		}


		// Assign the sticky session object to our template
		$template = \Skeleton\Core\Web\Template::get();
		$sticky_session = new \Skeleton\Core\Web\Session\Sticky();

		$template->add_environment('sticky_session', $sticky_session);

		// This is entirely optional, the use cases for having access to the
		// sticky session object in the module is are rather limited.
		$module->sticky_session = $sticky_session;

		// Clear the new session so we can start using it again
		\Skeleton\Core\Web\Session::clear_sticky();
		
		// Assign settings to template. Used for company information in header
		$template->assign('settings', Setting::get_as_array());

	}

	/**
	 * Teardown of the application
	 *
	 * @access private
	 */
	public static function teardown(\Skeleton\Core\Web\Module $module) {
		// Do your cleanup jobs here
	}
}
