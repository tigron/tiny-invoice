<?php
/**
 * Event
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Api\Event;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Session;
use \Skeleton\Database\Database;

class Application extends \Skeleton\Core\Event {

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function bootstrap(\Skeleton\Core\Web\Module $module) {
		try {
			$api_keys = \Setting::get_by_name('api_keys')->value;
		} catch (Exception $e) {
			$api_keys = '';
		}

		$keys = explode("\n", $api_keys);
		foreach ($keys as $key) {
			\Skeleton\Package\Api\Config::$api_keys[] = trim($key);
		}
	}
}
