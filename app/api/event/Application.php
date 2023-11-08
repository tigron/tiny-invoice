<?php
/**
 * Event
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Api\Event;

use \Skeleton\Application\Web\Template;
use \Skeleton\Core\Http\Session;
use \Skeleton\Database\Database;

class Application extends \Skeleton\Core\Application\Event\Application {

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function bootstrap(): bool {
		try {
			$api_keys = \Setting::get_by_name('api_keys')->value;
		} catch (Exception $e) {
			$api_keys = '';
		}

		$keys = explode("\n", $api_keys);
		foreach ($keys as $key) {
			\Skeleton\Package\Api\Config::$api_keys[] = trim($key);
		}

		return true;
	}
}
