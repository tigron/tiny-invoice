<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @version $Id$
 */

class Util_Autoload {

	/**
	 * Autoload classname
	 *
	 * @access public
	 * @param string $name
	 * @return string $name
	 */
	public static function classname($class) {
		$path = str_replace(' ', '/', ucwords(str_replace('_', ' ', $class))) . '.php';

		if (file_exists(LIB_PATH . '/base/' . $path)) {
			require_once LIB_PATH . '/base/' . $path;
		} elseif (file_exists(LIB_PATH . '/model/' . $path)) {
			require_once LIB_PATH . '/model/' . $path;
		}
	}
}