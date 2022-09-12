<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Util_Log {

	/**
	 * Log request log
	 *
	 * @param string $string
	 */
	public static function request($string) {
		$config = \Skeleton\Core\Config::get();
		file_put_contents($config->tmp_dir . '/log/request.log', '[' . date('d/m/Y H:i:s') . '] ' . $string . "\n", FILE_APPEND);
	}

	/**
	 * Log logins
	 *
	 * @param string $string
	 */
	public static function login($string) {
		$config = \Skeleton\Core\Config::get();
		file_put_contents($config->tmp_dir . '/log/login.log', '[' . date('d/m/Y H:i:s') . '] ' . $string . "\n", FILE_APPEND);
	}

	/**
	 * Log transaction
	 *
	 * @param string $string
	 */
	public static function transaction($string) {
		$config = \Skeleton\Core\Config::get();
		file_put_contents($config->tmp_dir . '/log/transaction.log', $string, FILE_APPEND);
	}

	/**
	 * Log queries
	 *
	 * @param string $string
	 */
	public static function query($string) {
		$config = \Skeleton\Core\Config::get();
		file_put_contents($config->tmp_dir . '/log/query.log', $string, FILE_APPEND);
	}
}
