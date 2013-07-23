<?php
/**
 * trait: Model
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 */

trait Model {
	/**
	 * @var int $id
	 * @access public
	 */
	public $id;

	/**
	 * Details
	 *
	 * @var array $details
	 * @access private
	 */
	private $details = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int $id
	 */
	public function __construct($id = null) {
		if ($id !== null) {
			$this->id = $id;
			$this->get_details();
		}
	}

	/**
	 * Get the details of this object
	 *
	 * @access private
	 */
	private function get_details() {
		$table = self::trait_get_database_table();

		if (!isset($this->id) OR $this->id === null) {
			throw new Exception('Could not fetch ' . $table . ' data: id not set');
		}

		$db = self::trait_get_database();
		$details = $db->getRow('SELECT * FROM ' . $table . ' WHERE id=?', array($this->id));

		if ($details === null) {
			throw new Exception('Could not fetch ' . $table . ' data: none found with id ' . $this->id);
		}

		$this->details = $details;
	}

	/**
	 * Set a detail
	 *
	 * @access public
	 * @param string $key
	 * @param mixex $value
	 */
	public function __set($key, $value) {
		// Check if the key we want to set exists in the disallow_set variable
		if (property_exists(get_class(), 'class_configuration') AND isset(self::$class_configuration['disallow_set'])) {
			if (is_array(self::$class_configuration['disallow_set'])) {
				if (in_array($key, self::$class_configuration['disallow_set'])) {
					throw new Exception('Can not set ' . $key . ' directly');
				}
			} else {
				throw new Exception('Improper use of disallow_set');
			}
		}

		if (is_object($value) AND property_exists($value, 'id')) {
			$this->details[$key . '_id'] = $value->id;
		}

		$this->details[$key] = $value;
	}

	/**
	 * Get a detail
	 *
	 * @access public
	 * @param string $key
	 * @return mixed $value
	 */
	public function __get($key) {
		if (isset($this->details[strtolower($key) . '_id']) AND class_exists($key)) {
			return $key::get_by_id($this->details[strtolower($key) . '_id']);
		}

		if (!isset($this->details[$key])) {
			throw new Exception('Unknown key requested: ' . $key);
		} else {
			return $this->details[$key];
		}
	}

	/**
	 * Isset
	 *
	 * @access public
	 * @param string $key
	 * @return bool $isset
	 */
	public function __isset($key) {
		if (isset($this->details[strtolower($key) . '_id']) AND class_exists($key)) {
			return true;
		} elseif (isset($this->details[$key])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Load array
	 *
	 * @access public
	 * @param array $details
	 */
	public function load_array($details) {
		foreach ($details as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * trait_get_database_config_name: finds out which database name we need to get
	 *
	 * @access public
	 * @return Database $database
	 */
	private static function trait_get_database() {
		if (property_exists(get_class(), 'class_configuration') AND isset(self::$class_configuration['database_config_name'])) {
			$db = Database::get(self::$class_configuration['database_config_name']);
		} else {
			$db = Database::get();
		}
		return $db;
	}

	/**
	 * trait_get_database_table: finds out which table we need to use
	 *
	 * @access public
	 * @return string $table
	 */
	private static function trait_get_database_table() {
		if (property_exists(get_class(), 'class_configuration') AND isset(self::$class_configuration['database_table'])) {
			return self::$class_configuration['database_table'];
		} else {
			return strtolower(get_class());
		}
	}
}
