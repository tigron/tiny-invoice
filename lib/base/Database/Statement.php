<?php
/**
 * Database Statement Class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */
class Database_Statement extends Mysqli_Stmt {

	/**
	 * Constructor
	 *
	 * @access Mysqli $database
	 * @access string $query
	 */
	public function __construct($database_resource, $query) {
		parent::__construct($database_resource, $query);
		if ($this->sqlstate != 0) {
			throw new Exception($this->error);
		}
	}

	/**
	 * Get columns of resultset
	 *
	 * @access public
	 * @return array $columns
	 */
	private function get_columns() {
		$meta = $this->result_metadata();

		$columns = array();
		while ($column = $meta->fetch_field()) {
			$columns[] = $column->db . '.' . $column->table . '.' . strtolower($column->name);
		}
		return $columns;
	}

	/**
	 * Fetch_assoc
	 *
	 * @access public
	 * @return array $data
	 */
	public function fetch_assoc() {
		$data = array();
		$params = array();

		foreach ($this->get_columns() as $column) {
			$params[$column] = &$data[$column];
		}

		$result = call_user_func_array(array($this, 'bind_result'), $params);
		$data = array();
		while ($this->fetch()) {
			$row = array();
			foreach ($params as $key => $value) {
				$key = 	substr($key, strrpos($key, '.') + 1);
				$row[$key] = $value;
			}
			$data[] = $row;
		}
		return $data;
	}
}
