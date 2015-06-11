<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Util_Mysql {

	/**
	 * Get table fields
	 *
	 * @access public
	 * @param string $table
	 * @return array $fields
	 */
	public static function get_table_fields($table) {
		$db = Database::Get();
		$fields = $db->listTableFields($table);
		return $fields;
	}

	/**
	 * Get table definition
	 *
	 * @access public
	 * @param string $table
	 * @param Database $db
	 * @return array $definition
	 */
	public static function get_table_definition($table, $db = null) {
		if ($db === null) {
			$db = Datbase::Get();
		}
		return $db->get_table_definition(strtolower($table));
	}

	/**
	 * Filter fields to insert/update table
	 *
	 * @access public
	 * @param string $table
	 * @param array $data
	 * @return $filtered_data
	 */
	public static function filter_table_data($table, $data) {
		$table_fields = Util::mysql_get_table_fields($table);
		$result = array();
		foreach ($table_fields as $field) {
			if (array_key_exists($field, $data)) {
				$result[$field] = $data[$field];
			}
		}

		if (count($data) == 0) {
			return array();
		}
		return $result;
	}

	/**
	 * Parse a mysql connection string
	 *
	 * @access public
	 * @param string $connection_string
	 * @return array $parameters
	 */
	public static function parse_connection_string($connection_string) {
		$connection_string = str_replace('mysql://', '', $connection_string);
		$connection_string = str_replace('mysqli://', '', $connection_string);
		list($first_part, $last_part) = explode('@', $connection_string);
		list($username, $password) = explode(':', $first_part);
		list($hostname, $database) = explode('/', $last_part);
		$parameters = array (	'username'	=>	$username,
								'password'	=>	$password,
								'hostname'	=>	$hostname,
								'database'	=>	$database);
		return $parameters;
	}

}
?>
