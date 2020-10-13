<?php
/**
 * Event
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Event;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Session;
use \Skeleton\Database\Database;

class Application extends \Skeleton\Core\Event {

	/**
	 * Bootstrap
	 *
	 * @access public
	 */
	public function bootstrap(\Skeleton\Core\Web\Module $module) {
		$this->start = microtime(true);

		// Bootsprap the application
		// Example: check if we need to log in, if we do and we aren't, redirect
		if ($module->is_login_required()) {
			if (!isset($_SESSION['user'])) {
				\Skeleton\Core\Web\Session::destroy();
				\Skeleton\Core\Web\Session::start();

				if (isset($_SERVER['REQUEST_URI'])) {
					$_SESSION['redirect_uri'] = $_SERVER['REQUEST_URI'];
				}

				\Skeleton\Core\Web\Session::redirect('/login');
			}

			\User::set($_SESSION['user']);
		}

		\Language::set($_SESSION['language']);

		if (is_callable([ $module, 'secure' ])) {
			$identifier = $module->secure();
			if (!$_SESSION['user']->has_permission($identifier)) {
				\Skeleton\Core\Web\Session::redirect('/403');
			}
		}

		// Assign the sticky session object to our template
		$template = \Skeleton\Core\Web\Template::get();
		$sticky_session = new \Skeleton\Core\Web\Session\Sticky();
		$template->add_environment('sticky_session', $sticky_session);

		// Assign settings to template. Used for company information in header
		$template->assign('settings', \Setting::get_as_array());
	}

	/**
	 * Teardown of the application
	 *
	 * @access private
	 */
	public function teardown(\Skeleton\Core\Web\Module $module = null) {
		$database = Database::get();
		$queries = $database->query_counter;

		$execution_time = microtime(true) - $this->start;

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$remote_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$remote_ip = $_SERVER['REMOTE_ADDR'];
		}

		$application = \Skeleton\Core\Application::get();
		\Util::log_request('Request: http://' . $application->hostname . $_SERVER['REQUEST_URI'] . ' -- IP: ' . $remote_ip . ' -- Queries: ' . $queries . ' -- Time: ' . $execution_time);
	}
}
