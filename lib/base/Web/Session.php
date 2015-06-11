<?php
/**
 * Session class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 */
class Web_Session {

	/**
	 * Start the Session
	 *
	 * @access public
	 */
	public static function start() {
		session_name('APP');
		session_start();
	}

	/**
	 * Redirect to
	 *
	 * @access public
	 */
	public static function redirect($url) {
		try {
			$url = Util::rewrite_reverse_link($url);
		} catch (Exception $e) { }

		if ($url == '' OR ($url[0] != '/' AND strpos($url, 'http') === false)) {
			$url = '/' . $url;
		}

		header('Location: '.$url);
		echo 'Redirecting to : '.$url;
		exit;
	}

	/**
	 * Destroy
	 *
	 * @access public
	 */
	public static function destroy() {
		session_destroy();
	}
}
