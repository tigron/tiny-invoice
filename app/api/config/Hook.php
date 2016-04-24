<?php
/**
 * Hooks
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 */

class Hook_Api {
	/**
	 * Bootstrap the application
	 *
	 * @access private
	 */
	public static function bootstrap(\Skeleton\Core\Web\Module $module) {
		try {
			$api_keys = Setting::get_by_name('api_keys')->value;
		} catch (Exception $e) {
			$api_keys = '';
		}

		$keys = explode("\n", $api_keys);
		foreach ($keys as $key) {
			\Skeleton\Package\Api\Config::$api_keys[] = trim($key);
		}
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
