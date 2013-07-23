<?php
/**
 * HTTP request Handler
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/base/Web/Module.php';
require_once LIB_PATH . '/base/Web/Media.php';
require_once LIB_PATH . '/base/Web/Session.php';
require_once LIB_PATH . '/base/Request/Log.php';
require_once LIB_PATH . '/model/Language.php';
require_once LIB_PATH . '/model/User.php';

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
		* Get the config
		*/
		$config = Config::Get();
		/**
		 * Define the application
		 */
		$applications = $config->applications;
		if (!isset($applications[$_SERVER['SERVER_NAME']])) {
			echo '404';
			return;
		}

		$application = $applications[$_SERVER['SERVER_NAME']];

		if (!is_array($application)) {
			$application = array('name' => $application);
		}

		$default_settings = array(
			'media' => true,
			'module' => array(
				'default' => 'index',
				'404' => '404',
			)
		);

		$application = array_merge($default_settings, $application);

		define('APP_NAME',		$application['name']);
		define('APP_PATH',		realpath(ROOT_PATH . '/app/' . $application['name']));
		define('MEDIA_PATH',	APP_PATH . '/media');
		define('MODULE_PATH',	APP_PATH . '/module');
		define('TEMPLATE_PATH',	APP_PATH . '/template');

		/**
		 * Check if this is an image or a media type
		 */
		if ($application['media'] == true) {
			Web_Media::Detect($request_parts);
		}

		/**
		 * Set the language to the template
		 */
		require_once LIB_PATH . '/base/Web/Template.php';

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

		// If the user explicitly asked for a language, try to set it
		if (isset($_GET['language'])) {
			try {
				$language = Language::get_by_name_short($_GET['language']);
				$_SESSION['language'] = $language;
			} catch (Exception $e) {
				$_SESSION['language'] = Language::get_by_name_short($config->default_language);
			}
		}

		// If the user explicitly asked for a language in the URI, try to set it
		if (isset($request_parts[0]) AND strlen($request_parts[0]) == 2) {
			try {
				$language = Language::get_by_name_short($request_parts[0]);
				$_SESSION['language'] = $language;
				array_shift($request_parts);
			} catch (Exception $e) { }
		}

		Language::set($_SESSION['language']);

		$template = Web_Template::Get();
		$template->assign('language', $_SESSION['language']);

		/**
		 * Look for the Module, try to match routes
		 */
		if (count($request_parts) == 0) {
			// If no module was requested, default to 'index'
			$request_parts[] = 'index';
		} elseif (isset($config->routes[APP_NAME])) {
			// Check if there is a route defined for the request
			$language = Language::get();

			$routes = array();
			foreach ($config->routes[APP_NAME] as $module => $route) {
				if (isset($route['routes'][$language->name_short])) {
					$routes[$route['routes'][$language->name_short]] = array(
						'target' => $module,
						'variables' => $route['variables'],
					);
				} elseif (isset($route['routes']['default'])) {
					$routes[$route['routes']['default']] = array(
						'target' => $module,
						'variables' => $route['variables'],
					);
				}
			}

			$route = '';
			foreach($request_parts as $key => $request_part) {
				$route .= '/' . $request_part;

				// Check if the request is defined by a route
				if (array_key_exists($route, $routes)) {
					$template->add_env('route', $route);

					// Check if the route matches without variables and if it's allowed to do so
					if ($route != '/' . implode($request_parts, '/')) {
						$variables = array_slice($request_parts, $key+1);

						$variable_match = null;
						foreach ($routes[$route]['variables'] as $variable_possibility) {
							if (count($variables) == substr_count($variable_possibility, '$')) {
								$variable_match = $variable_possibility;
								$template->add_env('variables', $variable_match);
								break;
							}
						}

						if ($variable_match === null) {
							throw new Exception('Route matches but no variable match found');
						}

						// Replace all the variables passed through the URI by the ones defined in the pattern
						$variable_parts = explode('/', $variable_match);
						foreach($variable_parts as $key => $variable_part) {
							$_GET[str_replace('$', '', $variable_part)] = $variables[$key];
						}

						$_REQUEST = array_merge($_REQUEST, $_GET);
					} elseif (!in_array('', $routes[$route]['variables'])) {
						break;
					}

					$request_parts = explode('/', $routes[$route]['target']);
					break;
				}
			}
		}

		$last_part = $request_parts[count($request_parts)-1];
		if (strpos($last_part,'?')) {
			$last_part = substr($last_part, 0, strpos($last_part, '?'));
			$request_parts[count($request_parts)] = $last_part;
		}

		header('Content-type: text/html; charset=utf-8');

		$possible_modules = array(
			// Module was called directly
			array(
				'file' => implode('/', $request_parts) . '.php',
				'classname' => 'Module_' . implode('_', $request_parts),
			),
			// Directory was called, start default module
			array(
				'file' => implode('/', $request_parts) . '/' . $application['module']['default'] . '.php',
				'classname' => 'Module_' . implode('_', $request_parts) . '_' . $application['module']['default'],
			),
			// Nothing found, start 404
			array(
				'file' => $application['module']['404'] . '.php',
				'classname' => 'Module_' . $application['module']['404'],
			),
		);

		foreach ($possible_modules as $possible_module) {
			$filename = strtolower(MODULE_PATH . '/' .  $possible_module['file']);
			if (file_exists($filename)) {
				require_once($filename);

				$module = new $possible_module['classname'];
				$module->accept_request();
				break;
			}
		}

		// Record debug information
		if ($config->debug == true) {
			$database = Database::get();
			$queries = $database->queries;
			$execution_time = microtime(true) - $start;

			Request_Log::log_request('Request: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . ' -- IP: ' . $_SERVER['REMOTE_ADDR'] . ' -- Queries: ' . $queries . ' -- Time: ' . $execution_time);
		}
	}
}
