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

		try {
			$new_link = Util::rewrite_reverse_link($data[3]);
		} catch (Exception $e) {
			return $data[0];
		}

		return str_replace($data[3], $new_link, $data[0]);
	}

	/**
	 * Do a reverse rewrite of a link
	 *
	 * @access public
	 * @param string $url
	 * @return string $reverse_rewrite
	 */
	public static function reverse_link($url) {
		$config = Config::Get();
		$language = Language::Get();
		$url = parse_url($url);

		$params = array();
		if (isset($url['query'])) {
			// Allow &amp; instead of &
			$url['query'] = str_replace('&amp;', '&', $url['query']);
			parse_str($url['query'], $params);
		}

		if (isset($config->routes[APP_NAME])) {
			$routes = $config->routes[APP_NAME];
		} else {
			throw new Exception('No routes found for current application');
		}

		$correct_route = null;
		if (isset($routes[$url['path']]['routes'][$language->name_short])) {
			$correct_route = $routes[$url['path']];
		} elseif (isset($routes[$url['path']]['routes']['default'])) {
			$correct_route = $routes[$url['path']];
		} else {
			throw new Exception('No available route found');
		}

		// We have a possible correct route
		$variables = $correct_route['variables'];
		$correct_variable_string = null;

		if (count($params) == 0 AND in_array('', $correct_route['variables'])) {
			$correct_variable_string = '';
		} else {
			foreach ($variables as $variable_string) {
				if (substr_count($variable_string, '$') == count($params)) {
					$correct_variable_string = $variable_string;
					break;
				}
			}
		}

		if ($correct_variable_string === null) {
			throw new Exception('Route found but variables incorrect');
		}

		// See if all variables match
		$correct_variables = explode('/', $correct_variable_string);
		$variables_matches = true;

		foreach ($correct_variables as $key => $correct_variable) {
			$correct_variable = str_replace('$', '', $correct_variable);
			if (!isset($params[$correct_variable]) AND $correct_variable != '') {
				$variables_matches = false;
				break;
			}
			$correct_variables[$key] = $correct_variable;
		}

		if (!$variables_matches) {
			throw new Exception('Route found but variables incorrect');
		}

		// Now build the new querystring
		if (isset($correct_route['routes'][$language->name_short])) {
			$querystring = $correct_route['routes'][$language->name_short];
		} else {
			$querystring = $correct_route['routes']['default'];
		}

		foreach ($correct_variables as $correct_variable) {
			if ($correct_variable != '') {
				$querystring .= '/' . $params[$correct_variable];
			}
		}

		// fragment (after '#') available?
		if (isset($url['fragment'])) {
			return $language->name_short . $querystring . '#' . $url['fragment'];
		} else {
			return $language->name_short . $querystring;
		}

	}
}
