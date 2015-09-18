<?php
/**
 * Database Proxy Class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/base/Database/Statement.php';

class Database_Proxy {
	/**
	 * PEAR DB Object
	 *
	 * @var DB
	 * @access public
	 */
	public $database = [];

	/**
	 * @var array
	 * @access public
	 */
	public $queries = 0;

	/**
	 * Query_log
	 *
	 * @var array
	 * @access public
	 */
	public $query_log = [];

	/**
	 * DatabaseProxy constructor
	 *
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Connect to the database
	 *
	 * @access public
	 * @param string $config_db
	 * @throws Exception Throws an Exception when the Database is unavailable
	 */
	public function connect($dsn) {
		$settings = parse_url($dsn);
		$settings['path'] = substr($settings['path'], 1);

		$this->database = new mysqli($settings['host'], $settings['user'], $settings['pass'], $settings['path']);
		$this->database->set_charset("utf8");
	}

	/**
	 * Stripslashes on result
	 *
	 * @access private
	 * @param array $data
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
	 * Customer stripslashes
	 *
	 * @access private
	 * @return string $slashed
	 * @param string $string
	 */
	private function custom_stripslashes($string) {
		if ($string === null) {
			return $string;
		} else {
			return $string;
			return stripslashes($string);
		}
	}

	/**
	 * Get table columns
	 *
	 * @access public
	 * @param string $table
	 */
	public function get_columns($table) {
		$statement = $this->get_statement('SHOW columns FROM ' . $this->quote_identifier($table) , []);
		$statement->execute();
		$result = $statement->fetch_assoc();

		$columns = [];
		foreach ($result as $row) {
			$columns[] = &$row['field'];
		}

		return $this->stripslashes_result($columns);
	}

	/**
	 * Get table definition
	 *
	 * @access public
	 * @param string $table
	 */
	public function get_table_definition($table) {
		$statement = $this->get_statement('DESC ' . $this->quote_identifier($table), []);
		$statement->execute();
		$result = $statement->fetch_assoc();
		return $result;
	}

	/**
	 * Get table indexes
	 *
	 * @access public
	 * @param string $table
	 */
	public function get_table_indexes($table) {
		$statement = $this->get_statement('SHOW INDEXES FROM ' . $this->quote_identifier($table), []);
		$statement->execute();
		$result = $statement->fetch_assoc();
		return $result;
	}

	/**
	 * Quote a variable so it can be used in a query
	 *
	 * @access public
	 * @param string $value
	 * @return array $quoted_values
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
		} else if (!is_numeric($values) OR (is_numeric($values) AND strpos(strtolower($values), 'e') !== false)) {
			// The reason why we are checking for the presence of the
			// letter "e" in the value in case it is numeric, is that
			// MySQL can not handle things like 49e6456 as numbers.
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
	 * @param string $field
	 * @return string $quoted_field
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
	 * Escape a variable
	 *
	 * @access public
	 * @param mixed $values
	 * @return string $escaped_field
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
	 * Get the Prepared statement
	 *
	 * @access private
	 * @return Database_Statement $statement
	 */
	private function get_statement($query, $params = []) {
		/**
		 * Memory problem when importing
		 * Only save 5000 queries
		 */
		if ($this->queries < 5000) {
			$query_log = [$query, $params];
			$this->query_log[] = $query_log;
			$this->queries++;
		}

		$statement = new Database_Statement($this->database, $query);

		if (count($params) == 0) {
			return $statement;
		}

		$refs = [];
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
				case is_null($param):
				case is_string($param):
					$types .= 's';
					break;
				case is_bool($param):
				case is_array($param):
				case is_object($param):
				case is_resource($param):
					throw new Exception("Unacceptable type used for bind_param.");
				default:
					throw new Exception("Unknown type used for bind_param.");
			}

			$refs[$key] = &$params[$key];
		}

		array_unshift($refs, $types);
		try {
			call_user_func_array([$statement, 'bind_param'], $refs);
		} catch (Exception $e) {
			throw new Exception('Error in query: ' . $query);
		}
		return $statement;
	}

	/**
	 * Wrapper around MDB2 to provide DB-like syntax to the consumer
	 *
	 * @access public
	 * @param string $query
	 * @param array $params
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

		return $result[0];
	}

	/**
	 * Get the first column of the resultset
	 *
	 * @access public
	 * @param string $query
	 * @param array $params
	 */
	public function get_column($query, $params = []) {
		$statement = $this->get_statement($query, $params);
		$statement->execute();
		$result = $statement->fetch_assoc();

		$col = [];
		foreach ($result as $row) {
			$col[] = array_shift($row);
		}

		return $col;
	}

	/**
	 * Insert query
	 *
	 * @access private
	 * @param array $params
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
	 * Update query
	 *
	 * @access public
	 * @param string $query
	 * @param array $params
	 * @param string $where
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
	 * Get a result with 1 cell
	 *
	 * @access public
	 * @param string $query
	 * @param array $params
	 */
	public function get_one($query, $params = []) {
		$statement = $this->get_statement($query, $params);
		$statement->execute();
		$result = $statement->fetch_assoc();

		if (count($result) == 0) {
			return null;
		}

		if (count($result) != 1) {
			throw new Exception('Result of getOne should only contain 1 row');
		}

		$row = array_shift($result);

		if (count($row) != 1) {
			throw new Exception('Result of getOne should only contain 1 column');
		}

		return array_shift($row);
	}

	/**
	 * Get the resultset in an associative array
	 *
	 * @access private
	 * @param string $query
	 * @param array $params
	 */
	public function get_all($query, $params = []) {
		$statement = $this->get_statement($query, $params);
		$statement->execute();
		return $statement->fetch_assoc();
	}

	/**
	 * Execute a query
	 *
	 * @access public
	 * @param string $query
	 * @param array $params
	 */
	public function query($query, $params = []) {
		$statement = $this->get_statement($query, $params);
		$statement->execute();
	}
}
