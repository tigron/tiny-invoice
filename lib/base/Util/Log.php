<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Util_Log {

	/**
	 * Log request log
	 *
	 * @param string $string
	 */
	public static function request($string) {
		file_put_contents(TMP_PATH . '/log/request.log', '[' . date('d/m/Y H:i:s') . '] ' . $string . "\n", FILE_APPEND);
	}

}
