<?php
/**
 * Database class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/base/Util.php';
require_once LIB_PATH . '/base/Database/Proxy.php';
require_once LIB_PATH . '/base/Database/Proxy/Compatibility/DB.php';

class Database {
	/**
	 * @var DatabaseProxy
	 * @access private
	 */
	private static $proxy = array();

	/**
	 * Private (disabled) constructor
	 *
	 * @access private
	 */
	private function __construct() { }

	/**
	 * Get function, returns a Database object, handles connects if needed
	 *
	 * @return DB
	 * @access public
	 */
	public static function Get($config_db = 'database') {
		if (!isset(self::$proxy[$config_db]) OR self::$proxy[$config_db] == false) {
			$config = Config::get();
			$dsn = $config->$config_db;

			self::$proxy[$config_db] = new Database_Proxy_Compatibility_DB();
			self::$proxy[$config_db]->connect($dsn);
		}
		return self::$proxy[$config_db];
	}
}
