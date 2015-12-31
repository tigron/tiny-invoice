<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Migration_20151000_093600_Init extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
			$dsn = $this->parse_dsn();
			$sql_file = realpath(dirname(__FILE__) . '/../config/database.sql');
			passthru('mysql -u ' . $dsn['username'] . ' -p' . $dsn['password'] . ' -h ' . $dsn['hostname'] . ' ' . $dsn['database'] . ' < ' . $sql_file);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}

	/**
	 * Parse Database scheme
	 *
	 * @return array
	 */
	private function parse_dsn() {
		$db_string = Config::get()->database;
		$db_string = str_replace('mysqli://', '', $db_string);
		list($username, $password) = explode(':', $db_string);
		list($password, $hostname) = explode('@', $password);
		list($hostname, $database) = explode('/', $hostname);
		$database = array(	'hostname' => $hostname,
							'username' => $username,
							'password' => $password,
							'database' => $database);
		return $database;
	}
}
