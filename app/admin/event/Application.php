<?php
/**
 * Event
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Event;

use \Skeleton\Application\Web\Template;
use \Skeleton\Core\Http\Session;
use \Skeleton\Database\Database;

class Application extends \Skeleton\Core\Application\Event\Application {

	/**
	 * Bootstrap
	 *
	 * @access public
	 */
	public function bootstrap(): bool {
		$this->start = microtime(true);
		return true;
	}

	/**
	 * Teardown of the application
	 *
	 * @access private
	 */
	public function teardown(): void {
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
