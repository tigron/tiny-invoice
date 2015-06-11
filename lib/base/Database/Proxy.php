<?php
/**
 * Database Proxy Class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Database_Proxy {
	/**
	 * @var mysqli $database The database connection to MySQL
	 * @access public
	 */
	public $database = null;

	/**
	 * @var int $queries The number of queries executed
	 * @access public
	 */
	public $queries = 0;

	/**
	 * @var array $query_log Array containing all executed queries
	 * @access public
	 */
	public $query_log = array();

	/**
	 * Database_Proxy constructor
	 *
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Connect to the database by providing a DSN
	 *
	 * @access public
	 * @param string $dsn The database source name you want to connect to
	 * @throws Exception Throws an Exception when the Database is unavailable
	 */
	public function connect($dsn) {
		$settings = parse_url($dsn);

		// If we can't even parse the DSN, don't bother
		if (!isset($settings['path']) OR !isset($settings['host']) OR !isset($settings['user'])) {
			throw new Exception('Could not connect to database: DSN incorrect');
		}

		// We don't support connecting to UNIX sockets the traditional way
		if ($settings['host'] == 'unix(') {
			throw new Exception('Could not connect to database: UNIX socket syntax is wrong');
		}

		$settings['path'] = substr($settings['path'], 1);
		$this->database = new mysqli($settings['host'], $settings['user'], $settings['pass'], $settings['path']);

		// If there is an error connecting to the database, stop doing what you're doing
		if ($this->database->connect_errno != 0) {
			throw new Exception('Could not connect to database: ' . $this->database->connect_error);
		}

		$this->database->set_charset('utf8');
	}

	/**
	 * Perform "stripslashes" on an array, usually a resultset
	 *
	 * @access private
	 * @param array $data The array to execute stripslashes on
	 * @return array $result
	 */
	private function stripslashes_result($data) {
		if ($data === null) {
			return $data;
		} elseif (is_array($data)) {
			foreach ($data as $key => $field) {
				if (is_array($field)) {
					$data[$key] = $this->stripslashes_result($field);
				} else {
					$data[$key] = $this->custom_stripslashes($field);
				}
			}
		} else {
			return $this->custom_stripslashes($data);
		}

		return $data;
	}

	/**
	 * Custom stripslashes implementation which ignores null strings
	 *
	 * @access private
	 * @param string $string The string to strip slashes from
	 * @return mixed $result The result of the operation is either a string or null
	 */
	private function custom_stripslashes($string) {
		if ($string === null) {
			return $string;
		} else {
			return stripslashes($string);
		}
	}

	/**
	 * Get all column names for a given table
	 *
	 * @access public
	 * @param string $table The table to fetch columns for
	 * @return array $result An array containing the columns
	 */
	public function get_columns($table) {
		$result = $this->get_table_definition($table);

		$columns = array();
		foreach ($result as $row) {
			$columns[] = &$row['field'];
		}

		return $this->stripslashes_result($columns);
	}

	/**
	 * Get table definition
	 *
	 * @access public
	 * @param string $table The table to fetch the definition for
	 * @return array $result An array containing the table definition
	 */
	public function get_table_definition($table) {
		$statement = $this->get_statement('DESC ' . $this->quote_identifier($table), array());
		$statement->execute();
		$result = $statement->fetch_assoc();
		return $result;
	}

	/**
	 * Quote a variable so it can be used in a query
	 *
	 * @access public
	 * @param string $value The variable to quote
	 * @return array $quoted_values The quoted result
	 */
	public function quote($values, $quotes = true) {
		if (is_array($values)) {
			foreach ($values as $key => $value) {
				$values[$key] = $this->quote($value, $quotes);
			}
		} else if ($values === null) {
			$values = 'NULL';
		} else if (is_bool($values)) {
			$values = $values ? 1 : 0;
		} else if (!is_numeric($values)) {
			$values = $this->escape($values);
			if ($quotes) {
				$values = '"' . $values . '"';
			}
		}

		return $values;
	}

	/**
	 * Quote a variable so it can be used in a field name in a query
	 *
	 * @access public
	 * @param string $field The field to quote
	 * @return string $quoted_field The quoted result
	 */
	public function quote_identifier($field) {
		$field = str_replace('`', '``', $field);
		$parts = explode('.', $field);

		foreach (array_keys($parts) as $k) {
			$parts[$k] = '`' . $parts[$k] . '`';
		}

		return implode('.', $parts);
	}

	/**
	 * Escape a variable with the usual real_escape_string implementation
	 *
	 * @access public
	 * @param mixed $values The values to escape, can be an array
	 * @return mixed $result The resulting escaped variable
	 */
	public function escape($values) {
		if (is_array($values)) {
			foreach ($values as $key => $value) {
				$values[$key] = $this->escape($value);
			}
			return $values;
		} else {
			return $this->database->real_escape_string($values);
		}
	}

	/**
	 * Get the prepared statement for a query and its parameters
	 *
	 * @access private
	 * @param string $query The query to prepare a statement for
	 * @param array $params Optional parameters to replace in the query
	 * @return Database_Statement $statement
	 * @throws Exception Throws an Exception when an unknown type is provided
	 */
	private function get_statement($query, $params = array()) {
		$query_log = array($query, $params);
		$this->query_log[] = $query_log;
		$this->queries++;

		$statement = new Database_Statement($this->database, $query);

		if (count($params) == 0) {
			return $statement;
		}

		$refs = array();
		$types = '';
		foreach ($params as $key => $param) {
			if (is_bool($param)) {
				if ($param == true) {
					$param = 1;
					$params[$key] = 1;
				} else {
					$param = 0;
					$params[$key] = 0;
				}
			}

			switch (true) {
				case is_integer($param):
					$types .= 'i';
					break;
				case is_double($param):
					$types .= 'd';
					break;
				case is_string($param):
				case is_null($param):
					$types .= 's';
					break;
				case is_bool($param):
				case is_array($param):
				case is_object($param):
				case is_resource($param):
					throw new Exception('Unacceptable type used for bind_param.');
				default:
					throw new Exception('Unknown type used for bind_param.');
			}

			$refs[$key] = &$params[$key];
		}

		array_unshift($refs, $types);
		call_user_func_array(array($statement, 'bind_param'), $refs);
		return $statement;
	}

	/**
	 * Get a single row from a resultset
	 *
	 * @access public
	 * @param string $query The query to execute
	 * @param array $params Optional parameters to replace in the query
	 * @return array $result The resulting associative array
	 * @throws Exception Throws an Exception when there is more than one row in a resultset
	 */
	public function get_row($query, $params) {
		$statement = $this->get_statement($query, $params);
		$statement->execute();

		$result = $statement->fetch_assoc();

		if (count($result) == 0) {
			return null;
		} else if (count($result) > 1) {
			throw new Exception('Resultset has more than 1 row');
		}

		return $this->stripslashes_result($result[0]);
	}

	/**
	 * Get the first column of the resultset
	 *
	 * @access public
	 * @param string $query The query to execute
	 * @param array $params Optional parameters to replace in the query
	 * @return array $result The resulting associative array
	 */
	public function get_column($query, $params = array()) {
		$statement = $this->get_statement($query, $params);
		$statement->execute();
		$result = $statement->fetch_assoc();

		$col = array();
		foreach ($result as $row) {
			$col[] = array_shift($row);
		}

		return $this->stripslashes_result($col);
	}

	/**
	 * Construct and execute an insert query
	 *
	 * @access public
	 * @param string $table The table to insert into
	 * @param array $params The values to insert into the table
	 */
	public function insert($table, $params) {
		$params = Util::mysql_filter_table_data($table, $params, $this);

		$keys = array_keys($params);
		foreach ($keys as $key => $value) {
			$keys[$key] = $this->quote_identifier($value);
		}

		$query = 'INSERT INTO ' . $this->quote_identifier($table) . ' (' . implode(',', $keys) . ') VALUES (';

		for ($i=0; $i < count($params); $i++) {
			if ($i > 0) {
				$query .= ', ';
			}
			$query .= '?';
		}

		$query .= ') ';
		$statement = $this->get_statement($query, $params);
		$statement->execute();
	}

	/**
	 * Construct and execute an update query
	 *
	 * @access public
	 * @param string $table The table to update
	 * @param array $params The values to update the table with
	 * @param string $where A WHERE-clause to add to the query
	 */
	public function update($table, $params, $where) {
		$params = Util::mysql_filter_table_data($table, $params, $this);

		$keys = array_keys($params);
		foreach ($keys as $key => $value) {
			$keys[$key] = $this->quote_identifier($value);
		}

		$query = 'UPDATE ' . $this->quote_identifier($table) . ' SET ';

		$first = true;
		foreach ($params as $key => $value) {
			if (!$first) {
				$query .= ', ';
			}
			$query .= $this->quote_identifier($key) . '= ?';
			$first = false;
		}

		$query .= ' WHERE ' . $where;

		$statement = $this->get_statement($query, $params);
		$statement->execute();
	}

	/**
	 * Get the resultset with a single row for a query
	 *
	 * @access public
	 * @param string $query The query to execute
	 * @param array $params Optional parameters to replace in the query
	 * @throws Exception Throws an Exception when the resultset contains more than one row or column
	 */
	public function get_one($query, $params = array()) {
		$statement = $this->get_statement($query, $params);
		$statement->execute();
		$result = $statement->fetch_assoc();

		if (count($result) == 0) {
			return null;
		}

		if (count($result) > 1) {
			throw new Exception('Result of get_one should only contain 1 row');
		}

		$row = array_shift($result);

		if (count($row) != 1) {
			throw new Exception('Result of get_one should only contain 1 column');
		}

		return $this->stripslashes_result(array_shift($row));
	}

	/**
	 * Get the resultset for a query in an associative array
	 *
	 * @access private
	 * @param string $query The query to execute
	 * @param array $params Optional parameters to replace in the query
	 */
	public function get_all($query, $params = array()) {
		$statement = $this->get_statement($query, $params);
		$statement->execute();
		return $this->stripslashes_result($statement->fetch_assoc());
	}

	/**
	 * Execute a query without returning any result
	 *
	 * @access public
	 * @param string $query The query to execute
	 * @param array $params Optional parameters to replace in the query
	 */
	public function query($query, $params = array()) {
		$statement = $this->get_statement($query, $params);
		$statement->execute();
	}

	/**
	 * Create a database
	 *
	 * @access public
	 * @param string $database The database name to create
	 */
	public function create_database($database) {
		$query = 'CREATE DATABASE ' . $this->quote_identifier($database);
		$statement = $this->get_statement($query);

		$statement->execute();
	}

	/**
	 * Drop a database
	 *
	 * @access public
	 * @param string $database The database name to drop
	 */
	public function drop_database($database) {
		$query = 'DROP DATABASE ' . $this->quote_identifier($database);
		$statement = $this->get_statement($query);

		$statement->execute();
	}

	/**
	 * Create a user and set his password
	 *
	 * @access public
	 * @param string $user name of the user that should be created
	 * @param string $host host the user will be connecting from, defaults to '%'
	 * @param string $password password of the user that we are creating, if it is omitted, a random password will be created
	 * @param bool $password_format_mysql determines if the password supplied is already in MySQL format or not
	 */
	public function create_user($user, $host = '%', $password = null, $password_format_mysql = false) {
		if ($password == null) {
			$password = md5(mt_rand() . microtime());
		}

		if ($password_format_mysql) {
			$password_format = 'PASSWORD';
		} else {
			$password_format = '';
		}

		$query = 'CREATE USER ' . $this->quote_identifier($user) . '@' . $this->quote($host) . ' IDENTIFIED BY ' . $password_format . ' ' . $this->quote($password);
		$statement = $this->get_statement($query);
		$statement->execute();

		$query = 'FLUSH PRIVILEGES';
		$statement = $this->get_statement($query);
		$statement->execute();
	}

	/**
	 * Drop a user and revoke all his permissions
	 *
	 * @access public
	 * @param string $user name of the user that should be dropped
	 * @param string $host host the user will be connecting from, defaults to '%'
	 */
	public function drop_user($user, $host = '%') {
		$query = 'REVOKE ALL PRIVILEGES, GRANT OPTION FROM ' . $this->quote_identifier($user) . '@' . $this->quote($host);
		$statement = $this->get_statement($query);
		$statement->execute();

		$query = 'DROP USER ' . $this->quote_identifier($user) . '@' . $this->quote($host);
		$statement = $this->get_statement($query);
		$statement->execute();

		$query = 'FLUSH PRIVILEGES';
		$statement = $this->get_statement($query);
		$statement->execute();
	}

	/**
	 * Update a user's password
	 *
	 *
	 * @access public
	 * @param string $user name of the user that should be updated
	 * @param string $host host the user will be connecting from, defaults to '%'
	 * @param string $password the new password of the user, if it is omitted, a random password will be created
	 */
	public function update_user_password($user, $host = '%', $password = null, $password_format_mysql = false) {
		if ($password == null) {
			$password = md5(mt_rand() . microtime());
		}

		if ($password_format_mysql) {
			$password = $this->quote($password);
		} else {
			$password = 'PASSWORD(' . $this->quote($password) . ')';
		}

		$query = 'SET PASSWORD FOR ' . $this->quote_identifier($user) . '@' . $this->quote($host) . ' = ' . $password;
		$statement = $this->get_statement($query);
		$statement->execute();

		$query = 'FLUSH PRIVILEGES';
		$statement = $this->get_statement($query);
		$statement->execute();
	}

	/**
	 * Grant all privileges on a database to a user
	 *
	 * @access public
	 * @param string $database name of the database that we are granting permissions on
	 * @param string $user user that will get the permissions
	 * @param string $host host the user will be connecting from, defaults to '%'
	 */
	public function grant_all_privileges($database, $user, $host = '%') {
		$query = 'GRANT ALL PRIVILEGES ON ' . $this->quote_identifier($database) . '.* TO ' . $this->quote_identifier($user) . '@' . $this->quote($host);
		$statement = $this->get_statement($query);
		$statement->execute();

		$query = 'FLUSH PRIVILEGES';
		$statement = $this->get_statement($query);
		$statement->execute();
	}

	/**
	 * Revoke all privileges on a database from a user
	 *
	 * @access public
	 * @param string $database name of the database that we are revoking permissions on
	 * @param string $user user that will get the permissions
	 * @param string $host host the user will be connecting from, defaults to '%'
	 */
	public function revoke_all_privileges($database, $user, $host = '%') {
		$query = 'REVOKE ALL PRIVILEGES ON ' . $this->quote_identifier($database) . '.* FROM ' . $this->quote_identifier($user) . '@' . $this->quote($host);
		$statement = $this->get_statement($query);
		$statement->execute();

		$query = 'FLUSH PRIVILEGES';
		$statement = $this->get_statement($query);
		$statement->execute();
	}
}