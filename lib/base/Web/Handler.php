<?php
/**
 * HTTP request Handler
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

require_once LIB_PATH . '/base/Web/Session.php';

class Web_Handler {
	/**
	 * Handle the request and send it to the correct module
	 *
	 * @access public
	 */
	public static function run() {
		/**
		 * Record the start time in microseconds
		 */
		$start = microtime(true);
		mb_internal_encoding('utf-8');

		/**
		 * Start the session
		 */
		Web_Session::start();

		/**
		 * Hide PHP powered by
		 */
		header('X-Powered-By: Me');

		/**
		 * Determine the request type
		 */
		$query_string = explode('?', $_SERVER['REQUEST_URI']);
		$request_parts = explode('/', $query_string[0]);
		if (isset($query_string[1])) {
			$request_parts[count($request_parts)-1] = $request_parts[count($request_parts)-1] . '?' . $query_string[1];
		}

		foreach ($request_parts as $key => $part) {
			if (strpos($part, '?') !== false) {
				$request_parts[$key] = substr($part, 0, strpos($part, '?'));
				$part = substr($part, 0, strpos($part, '?'));
			}

			if ($part == '') {
				unset($request_parts[$key]);
			}
		}

		// reorganize request_parts array
		$request_parts = array_merge($request_parts, array());

		/**
		 * Define the application
		 */
		try {
			$application = Application::Detect();
		} catch (Exception $e) {
			echo '404';
			return;
		}

		/**
		* Get the config
		*/
		$config = Config::Get();

		/**
		 * Handle the media
		 */
		Web_Media::Detect($request_parts);

		/**
		 * Find the module to load
		 */
		try {
			$module = $application->route($query_string[0]);
		} catch (Exception $e) {

			// So there is no route defined.

			/**
			 * 1. Try to look for the exact module
			 * 2. Take the default module
			 * 3. Load 404 module
			 */
			$filename = implode('/', $request_parts);

			if (file_exists($application->module_path . '/' . $filename . '.php')) {
				require $application->module_path . '/' . $filename . '.php';
				$classname = 'Web_Module_' . implode('_', $request_parts);
			} elseif (file_exists($application->module_path . '/' . $filename . '/' . $config->module_default . '.php')) {
				require $application->module_path . '/' . $filename . '/' . $config->module_default . '.php';
				if ($filename == '') {
					$classname = 'Web_Module_' . $config->module_default;
				} else {
					$classname = 'Web_Module_' . implode('_', $request_parts) . '_' . $config->module_default;
				}
			} elseif (file_exists($application->module_path . '/' . $config->module_404 . '.php')) {
				require $application->module_path . '/' . $config->module_404 . '.php';
				$classname = 'Web_Module_' . $config->module_404;
			} else {
				header('HTTP/1.0 404 Module not found');
				exit;
			}
			$module = new $classname;
		}

		/**
		 * Set language
		 */
		// Set the language to something sensible if it isn't set yet
		if (!isset($_SESSION['language'])) {
			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				$languages = Language::get_all();

				foreach ($languages as $language) {
					if (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], $language->name_short) !== false) {
						$language = $language;
						$_SESSION['language'] = $language;
					}
				}
			}

			if (!isset($_SESSION['language'])) {
				$language = Language::get_by_name_short($config->default_language);
				$_SESSION['language'] = $language;
			}
		}

		if (isset($_GET['language'])) {
			try {
				$language = Language::get_by_name_short($_GET['language']);
				$_SESSION['language'] = $language;
			} catch (Exception $e) {
				$_SESSION['language'] = Language::get_by_name_short($config->default_language);
			}
		}
		$application->language = $_SESSION['language'];

		$module->accept_request();

		// Record debug information
		$database = Database::get();
		$queries = $database->queries;
		$execution_time = microtime(true) - $start;

		Util::log_request('Request: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ' -- IP: ' . $_SERVER['REMOTE_ADDR'] . ' -- Queries: ' . $queries . ' -- Time: ' . $execution_time);

	}
}
