<?php

declare(strict_types=1);

/**
 * Event
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Event;

use Skeleton\Core\Http\Session;

class Module extends \Skeleton\Application\Web\Event\Module {
	/**
	 * Bootstrap the module
	 *
	 * @access public
	 * @param \Skeleton\Application\Web\Module $module
	 */
	public function bootstrap(\Skeleton\Application\Web\Module $module): void {

		// Bootsprap the application
		// Example: check if we need to log in, if we do and we aren't, redirect
		if ($module->is_login_required()) {
			if (!isset($_SESSION['user'])) {
				\Skeleton\Core\Http\Session::destroy();
				\Skeleton\Core\Http\Session::start();

				if (isset($_SERVER['REQUEST_URI'])) {
					$_SESSION['redirect_uri'] = $_SERVER['REQUEST_URI'];
				}

				\Skeleton\Core\Http\Session::redirect('/login');
			}

			\User::set($_SESSION['user']);
		}

		\Language::set($_SESSION['language']);

		if (is_callable([ $module, 'secure' ])) {
			$identifier = $module->secure();
			if (!$_SESSION['user']->has_permission($identifier)) {
				\Skeleton\Core\Http\Session::redirect('/403');
			}
		}

		// Assign the sticky session object to our template
		$template = \Skeleton\Application\Web\Template::get();
		$sticky_session = new \Skeleton\Core\Http\Session\Sticky();
		$template->add_environment('sticky_session', $sticky_session);

		// Assign settings to template. Used for company information in header
		$template->assign('settings', \Setting::get_as_array());
	}
}