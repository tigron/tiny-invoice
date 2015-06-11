<?php
/**
 * Util_Rewrite class
 *
 * Contains rewrite utils
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Util_Rewrite {

	/**
	 * Reverse rewrite
	 *
	 * @access public
	 * @param string $html
	 * @return string $html_with_reverse_rewrite
	 */
	public static function reverse_html($html) {
		$html = preg_replace_callback('@\<([^>]*) (href|src|action)="/([^"]*)?"@iU', 'Util_Rewrite::reverse_html_callback', $html);
		return $html;
	}

	/**
	 * Reverse rewrite callback for regexp
	 *
	 * @access private
	 * @param array $data
	 * @return string $string
	 */
	public static function reverse_html_callback($data) {
		if (!isset($data[3])) {
			return $data[0];
		}
		$new_link = Util::rewrite_reverse_link($data[3]);
		return str_replace($data[3], $new_link, $data[0]);
	}

	/**
	 * Do a reverse rewrite of a link
	 *
	 * @access public
	 * @param string $url
	 * @return string $reverse_rewrite
	 */
	public static function reverse_link($url_raw) {
		$url = parse_url($url_raw);

		$params = array();
		if (isset($url['query'])) {
			// Allow &amp; instead of &
			$url['query'] = str_replace('&amp;', '&', $url['query']);
			parse_str($url['query'], $params);
		}

		$application = Application::Get();
		$language = $application->language;
		$routes = $application->config->routes;

		/**
		 * Add language to the known parameters
		 */
		$params['language'] = $language->name_short;

		/**
		 * Search for the requested module
		 */
		if (!isset($url['path'])) {
			return $url_raw;
		}
		if ($url['path'] != '' AND $url['path'][0] == '/') {
			$url['path'] = substr($url['path'], 1);
		}
		$module_name = 'web_module_' . str_replace('/', '_', $url['path']);

		$module_defined = false;

		if (isset($routes[$module_name])) {
			$module_defined = true;
		} elseif (isset($routes[$module_name . '_index'])) {
			$module_name = $module_name . '_index';
			$module_defined = true;
		}

		if (!$module_defined) {
			return $url_raw;
		}

		$routes = $routes[$module_name];

		$correct_route = null;
		foreach ($routes as $route) {
			$route_parts = explode('/', $route);
			$route_part_matches = 0;

			foreach ($route_parts as $key => $route_part) {
				if (trim($route_part) == '') {
					unset($route_parts[$key]);
					continue;
				}
				if ($route_part[0] != '$') {
					$route_part_matches++;
					continue;
				}
				/**
				 * $language[en,nl] => language[en,nl]
				 */
				$route_part = substr($route_part, 1);

				/**
				 * Fetch required values
				 */
				$required_values = array();
				preg_match_all('/(\[(.*?)\])/', $route_part, $matches);
				if (count($matches[2]) > 0) {
					/**
					 * There are required values, parse them
					 */
					$required_values = explode(',', $matches[2][0]);
					$route_part = str_replace($matches[0][0], '', $route_part);
					$route_parts[$key] = '$' . $route_part;
				}

				if (isset($params[$route_part])) {
					/**
					 * if there are no required values => Proceed
					 */
					if (count($required_values) == 0) {
						$route_part_matches++;
						continue;
					}

					/**
					 * Check the required values
					 */
					$values_ok = false;
					foreach ($required_values as $required_value) {
						if ($required_value == $params[$route_part]) {
							$values_ok = true;
						}
					}

					if ($values_ok) {
						$route_part_matches++;
						continue;
					}
				}
			}

			if ($route_part_matches == count($route_parts)) {
				$correct_route = $route_parts;
			}
		}

		if ($correct_route === null) {
			return $url_raw;
		}

		$new_url = '';
		foreach ($correct_route as $url_part) {
			if ($url_part[0] !== '$') {
				$new_url .= '/' . $url_part;
				continue;
			}

			$url_part = substr($url_part, 1);
			$new_url .= '/' . $params[$url_part];
			unset($params[$url_part]);
		}

		/**
		 * If the first character is a /, remove it
		 */
		if ($new_url[0] == '/') {
			$new_url = substr($new_url, 1);
		}

		if (count($params) > 0) {
			$new_url .= '?' . urldecode(http_build_query($params));
		}

		/**
		 * Is there a fragment ('#') available?
		 */
		if (isset($url['fragment'])) {
			$new_url .= '#' . $url['fragment'];
		}

		return $new_url;
	}
}
