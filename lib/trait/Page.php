<?php
/**
 * trait: Page
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 */

trait Page {
	/**
	 * Get paged
	 *
	 * @access public
	 * @param string $sort
	 * @param string $direction
	 * @param int $page
	 * @param int $all
	 * @param array $extra_conditions
	 * @param array $extra_joins
	 */
	public static function get_paged($sort = 1, $direction = 'asc', $page = 1, $extra_conditions = [], $all = false, $extra_joins = []) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$where = self::trait_get_search_where($extra_conditions, $extra_joins);
		$joins = self::trait_get_link_tables();

		// $sort is sometimes passed as null, which looks better, but we want 1
		// by default
		if ($sort == null) {
			$sort = 1;
		}

		$object = new ReflectionClass(get_class());
		if (is_callable($sort)) {
			$sorter = 'object';
		} elseif ($object->hasMethod($sort)) {
			$sorter = 'object';
		} else {
			$sorter = 'db';
		}

		$config = Config::Get();

		if (!$all) {
			$limit = $config->items_per_page;
		} else {
			$limit = 1000;
		}

		if ($page < 1) {
			$page = 1;
		}

		if (strtolower($direction) != 'asc') {
			$direction = 'desc';
		}

		$sql = 'SELECT DISTINCT(' . $table . '.id) ' . "\n";
		$sql .= 'FROM `' . $table . '`' . "\n";

		// Determine if the sort is on the based table or on a joined table
		// Make the JOINs mandatory if it is on a joined one
		$join_mandatory = true;

		if (is_object($sort) or strpos($sort, $table . '.') === 0 or strpos($sort, '.') === false) {
			$join_mandatory = false;
		}

		// If the WHERE clause is empty, we don't need any joins at all
		// unless we are sorting on a field not in the main table
		if ($where != '' or $join_mandatory) {
			foreach ($joins as $join) {
				$sql .= 'LEFT OUTER JOIN `' . $join . '` on `' . $table . '`.' . $join . '_id = ' . $join . '.id ' . "\n";
			}

			foreach ($extra_joins as $extra_join) {
				$sql .= 'LEFT OUTER JOIN `' . $extra_join[0] . '` on `' . $extra_join[0] . '`.' . $extra_join[1] . ' = ' . $extra_join[2] . "\n";
			}
		}

		if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
			$sql .= 'LEFT OUTER JOIN object_text ON object_text.classname LIKE "' . get_class() . '" AND object_text.object_id=' . $table . '.id ';
			if ($sorter == 'db' AND in_array($sort, self::$object_text_fields)) {
				if (isset($extra_conditions['language_id'])) {
					$language_id = $extra_conditions['language_id'][1];
				} else {
					$language_id = Application::Get()->language->id;
				}

				$sql .= 'AND object_text.label = ' . $db->quote($sort) . ' AND object_text.language_id = ' . $language_id . ' ';

				$sort = 'object_text.content';
			}
			$sql .= "\n";
		}

		$sql .= 'WHERE 1 ' . $where . "\n";

		if ($sorter == 'db') {
			if (strpos($sort, '.') === false AND $sort != 1) {
				$sort = $table . '.' . $sort;
			}

			$sql .= 'ORDER BY ' . $sort . ' ' . $direction;
		}

		if ($all !== true AND $sorter == 'db') {
			$sql .= ' LIMIT ' . ($page-1)*$limit . ', ' . $limit;
		}

		$ids = $db->get_column($sql);

		$objects = [];
		foreach ($ids as $id) {
			$objects[] = self::get_by_id($id);
		}

		foreach ($extra_conditions as $key => $value) {
			foreach ($objects as $o_key => $object) {
				if (!is_callable([$object, $key])) {
					continue;
				}

				try {
					if (call_user_func_array([$object, $key], $value)) {
						continue;
					}
				} catch (Exception $e) {
					continue;
				}

				unset($objects[$o_key]);
			}
		}

		if ($sorter == 'object') {
			$objects = Util::object_sort($objects, $sort, $direction);
			$objects = array_slice($objects, ($page-1)*$limit, $limit);
		}

		return $objects;
	}

	/**
	 * Count the number of results
	 *
	 * @access public
	 * @param array $extra_conditions
	 * @param array $extra_joins
	 * @return int $count
	 */
	public static function count($extra_conditions = [], $extra_joins = []) {
		return self::trait_get_aggregate('count', $extra_conditions, $extra_joins);
	}

	/**
	 * Get the sum for a given column
	 *
	 * @access public
	 * @param array $extra_conditions
	 * @param array $extra_joins
	 * @return int $count
	 */
	public static function sum($field, $extra_conditions = [], $extra_joins = []) {
		return self::trait_get_aggregate('sum', $extra_conditions, $extra_joins, ['field' => $field]);
	}

	/**
	 * Get the table definition for a given table
	 *
	 * This also adds some additional data to the information, such as a
	 * simplified version of the type.
	 *
	 * @access private
	 * @param string $table
	 * @return array $definitions
	 */
	private static function trait_get_table_definition($table) {
		$db = self::trait_get_database();
		$definitions = $db->get_table_definition($table);
		$indexes = $db->get_table_indexes($table);

		foreach ($definitions as $key => $definition) {
			// Only keep the relevant definitions
			if (substr($definition['field'], -3) == '_id') {
				unset($definitions[$key]);
				continue;
			}

			// Define a 'simple_type', only telling us what kind of data
			// we are dealing with.
			$definitions[$key]['simple_type'] = null;

			if (strpos($definition['type'], '(') !== false) {
				$type = substr($definition['type'], 0, strpos($definition['type'], '('));
			} else {
				$type = $definition['type'];
			}

			switch ($type) {
				case 'text':
				case 'mediumtext':
				case 'longtext':
				case 'varchar':
				case 'enum':
					$definitions[$key]['simple_type'] = 'text';
					break;
				case 'date':
				case 'datetime':
				case 'time':
					$definitions[$key]['simple_type'] = 'date';
					break;
				case 'tinyint':
				case 'decimal':
				case 'double':
				case 'mediumint':
				case 'int':
					$definitions[$key]['simple_type'] = 'number';
					break;
			}

			// Find out if we have some kind of index enabled on this field
			$definitions[$key]['has_index'] = false;
			$definitions[$key]['index_type'] = null;

			foreach ($indexes as $index) {
				if ($index['column_name'] == $definition['field']) {
					$definitions[$key]['has_index'] = true;
					$definitions[$key]['index_type'] = strtolower($index['index_type']);
				}
			}
		}

		return $definitions;
	}

	/**
	 * Find out how to compare the field and the given value
	 *
	 * @access private
	 * @param string $field
	 * @param string $value
	 * @param array $definition
	 * @return string $where
	 */
	private static function trait_get_comparison($field, $value, $definition) {
		$db = self::trait_get_database();

		$where = '';

		if ($definition['simple_type'] == 'text') {
			$where = 'OR ' . $field . ' LIKE \'%' . $db->quote($value, false) . '%\' ' . "\n\t";
		} elseif ($definition['simple_type'] == 'number' and is_numeric($value)) {
			$where = 'OR ' . $field . ' = \'' . $db->quote($value, false) . '\' ' . "\n\t";
		}

		return $where;
	}

	/**
	 * Get where clause for paging
	 *
	 * @access public
	 * @param array $extra_conditions
	 * @param array $extra_joins
	 */
	private static function trait_get_search_where($extra_conditions = [], $extra_joins = []) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$definitions = self::trait_get_table_definition($table);
		$joins = self::trait_get_link_tables();

		$where = '';

		$object = new ReflectionClass(get_class());
		foreach ($extra_conditions as $key => $value) {
			if ($key != '%search%' AND !is_callable($key) AND !$object->hasMethod($key)) {
				if ($value[0] == 'IN') {
					if (is_array($value[1])) {
						$list = implode($value[1], ', ');
					} else {
						$list = $value[1];
					}

					$where .= 'AND ' . $db->quoteidentifier($key) . ' IN (' . $list . ')' . "\n\t";
				} elseif (is_array($value[1])) {
					$where .= 'AND (0';
					foreach ($value[1] as $element) {
						$where .= ' OR ' . $db->quoteidentifier($key) . ' ' . $value[0] . ' ' . $db->quote($element);
					}
					$where .= ') ';
				} elseif ($value[0] == 'BETWEEN') {
					$where .= 'AND ' . $db->quoteidentifier($key) . ' BETWEEN ' . $db->quote($value[1]) . ' AND ' . $db->quote($value[2]) . "\n\t";
				} else {
					$where .= 'AND ' . $db->quoteidentifier($key) . ' ' . $value[0] . ' ' . $db->quote($value[1]) . ' ' . "\n\t";
				}
			}
		}

		if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
			// Object Text fields: language_id
			if (isset($extra_conditions['language_id'])) {
				$where .= 'AND object_text.language_id = ' . $db->quote($extra_conditions['language_id'][1]) . ' ' . "\n\t";
			}
		}

		if (isset($extra_conditions['%search%']) AND $extra_conditions['%search%'] != '') {
			$where .= 'AND (1 ';

			$ec_search = explode(' ', trim($extra_conditions['%search%']));

			foreach ($ec_search as $element) {
				$definitions = self::trait_get_table_definition($table);
				$where .= ' AND (0 ';

				foreach ($definitions as $definition) {
					$where .= self::trait_get_comparison($table . '.' . $definition['field'], $element, $definition);
				}

				foreach ($joins as $join) {
					$definitions = self::trait_get_table_definition($join);


					foreach ($definitions as $definition) {
						$where .= self::trait_get_comparison($join . '.' . $definition['field'], $element, $definition);
					}
				}

				foreach ($extra_joins as $extra_join) {
					$definitions = self::trait_get_table_definition($extra_join[0]);

					foreach ($definitions as $definition) {
						$where .= self::trait_get_comparison($extra_join[0] . '.' . $definition['field'], $element, $definition);
					}
				}

				if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
					$where .= 'OR object_text.content LIKE \'%' . $db->quote($element, false) . '%\' ' . "\n\t";
				}

				$where .= ') ';
			}

			$where .= ') ' . "\n";
		}

		if (strlen($where) > 0) {
			return "\n\t" . $where;
		} else {
			return '';
		}
	}

	/**
	 * Calculate an aggregate
	 *
	 * @access public
	 * @param string $type
	 * @param array $extra_conditions
	 * @param array $extra_joins
	 * @param array $extra_parameters
	 * @return int $count
	 */
	private static function trait_get_aggregate($type, $extra_conditions = [], $extra_joins = [], $extra_parameters = []) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$where = self::trait_get_search_where($extra_conditions, $extra_joins);
		$joins = self::trait_get_link_tables();

		$join_mandatory = false;

		switch ($type) {
			case 'count':
				$sql = 'SELECT COUNT(DISTINCT(' . $table . '.id)) ';
				break;
			case 'sum':
				if (!isset($extra_parameters['field'])) {
					throw new Exception('Aggregate sum needs a field');
				}

				$sql = 'SELECT SUM(' . $extra_parameters['field'] . ') ';
				$join_mandatory = true;
				break;
			default:
				throw new Exception('Unsupported aggregate');
		}

		$sql .= 'FROM `' . $table . '` ';

		if ($where != '' or $join_mandatory == true) {
			foreach ($joins as $join) {
				$sql .= 'LEFT OUTER JOIN `' . $join . '` on `' . $table . '`.' . $join . '_id = ' . $join . '.id ';
			}

			foreach ($extra_joins as $extra_join) {
				$sql .= 'LEFT OUTER JOIN `' . $extra_join[0] . '` on `' . $extra_join[0] . '`.' . $extra_join[1] . ' = ' . $extra_join[2] . "\n";
			}

			if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
				$sql .= 'LEFT OUTER JOIN object_text on object_text.classname LIKE "' . get_class() . '" AND object_text.object_id=' . $table . '.id ' . "\n";
			}
		}

		$sql .= 'WHERE 1 ' . $where;

		$count = $db->get_one($sql);

		return $count;
	}
}
