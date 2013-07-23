<?php
/**
 * Util class
 *
 * Contains utilities for Application handling
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Util_Debug {
	/**
	 * Mail
	 *
	 * @access public
	 * @param string $content
	 * @param string $subject
	 */
	public static function mail($content, $subject = 'Debug message') {
		$config = Config::Get();
		mail($config->errors_to, $subject, $content);
	}
}
