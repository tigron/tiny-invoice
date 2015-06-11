<?php
/**
 * trait: Page
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 */

trait Page {
	/**
	 * Get where clause for paging
	 *
	 * @access public
	 * @param array $extra_conditions
	 * @param array $extra_joins
	 */
	private static function get_search_where($extra_conditions = [], $extra_joins = []) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$fields = Util::mysql_get_table_fields($table);
		$joins = self::trait_get_link_tables();
		$joins = array_merge($joins, $extra_joins);

		$extra_conditions_raw = $extra_conditions;
		$where = "\n\t";

		foreach ($extra_conditions as $key => $value) {
			if ($key != '%search%') {

				if (is_array($value[1])) {
					$where .= 'AND (0';
					foreach ($value[1] as $element) {
						$where .= ' OR ' . $db->quoteidentifier($key) . ' ' . $value[0] . ' ' . $db->quote($element);
					}
					$where .= ') ';
				} else {
					$where .= 'AND ' . $db->quoteidentifier($key) . ' ' . $value[0] . ' ' . $db->quote($value[1]) . ' ' . "\n\t";
				}
			}
		}

		if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
			// Object Text fields: language_id
			if (isset($extra_conditions_raw['language_id'])) {
				$where .= 'AND object_text.language_id = ' . $db->quote($extra_conditions_raw['language_id'][1]) . ' ' . "\n\t";
			}
		}

		if (isset($extra_conditions['%search%']) AND $extra_conditions['%search%'] != '') {
			$where .= 'AND (0 ';

			$ec_search = explode(' ', trim($extra_conditions['%search%']));

			foreach ($fields as $field) {
				foreach ($ec_search as $element) {
					$where .= 'OR ' . $table . '.' . $field . " LIKE '%"  . $element . "%' " . "\n\t";
				}
			}

			foreach ($joins as $join) {
				$fields = Util::mysql_get_table_fields($join);
				foreach ($fields as $field) {
					foreach ($ec_search as $element) {
						$where .= 'OR ' . $join . '.' . $field . " LIKE '%"  . $element . "%' " . "\n\t";
					}
				}
			}

			foreach ($extra_joins as $extra_join) {
				$fields = Util::mysql_get_table_fields($extra_join[0]);
				foreach ($fields as $field) {
					foreach ($ec_search as $element) {
						$where .= 'OR ' . $extra_join[0] . '.' . $field . " LIKE '%"  . $element . "%' " . "\n\t";
					}
				}
			}

			if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
				foreach ($ec_search as $element) {
					$where .= "OR object_text.content LIKE '%" . $element . "%' " . "\n\t";
				}
			}

			$where .= ') ' . "\n";
		}

		return $where;
	}

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
	public static function get_paged($sort = 1, $direction = 'asc', $page = 1, $limit, $extra_conditions = [], $all = false, $extra_joins = []) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$where = self::get_search_where($extra_conditions, $extra_joins);
		$joins = self::trait_get_link_tables();

		$object = new self();
		if (is_callable($sort)) {
			$sorter = 'object';
		} elseif (method_exists($object, $sort) AND is_callable(array($object, $sort))) {
			$sorter = 'object';
		} else {
			$sorter = 'db';
		}

		$config = Config::Get();

		if (is_null($limit)) {
			$limit = $config->items_per_page;
		}

		if ($all) {
			$limit = 1000;
		}

		if ($page < 1) {
			$page = 1;
		}

        if ($direction != 'asc') {
			$direction = 'desc';
        }

		$sql  = 'SELECT DISTINCT(' . $table . '.id) ' . "\n";
		$sql .= 'FROM `' . $table . '`' . "\n";
		foreach ($joins as $join) {
			$sql .= 'LEFT OUTER JOIN `' . $join . '` on `' . $table . '`.' . $join . '_id = ' . $join . '.id '  . "\n";
		}

		foreach ($extra_joins as $extra_join) {
			$sql .= 'LEFT OUTER JOIN `' . $extra_join[0] . '` on `' . $extra_join[0] . '`.' . $extra_join[1] . ' = ' . $extra_join[2] . "\n";
		}

		if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
			$sql .= 'LEFT OUTER JOIN object_text ON object_text.classname LIKE "' . get_class() . '%" AND object_text.object_id=' . $table . '.id ';
			if ($sorter == 'db' AND in_array($sort, self::$object_text_fields)) {
				$sql .= 'AND object_text.label = ' . $db->quote($sort) . ' AND object_text.language_id = ' . Application::Get()->language->id . ' ';

				$sort = 'object_text.content';
			}
			$sql .= "\n";
		}

		$sql .= 'WHERE 1 ' . $where . "\n";

		if ($sorter == 'db') {
			$sql .= 'ORDER BY ' . $sort . ' ' . $direction;
		}

		if ($all !== true AND $sorter == 'db') {
			$sql .= ' LIMIT ' . ($page-1)*$limit . ', ' . $limit;
		}

		$ids = $db->getCol($sql);
		$objects = [];
		foreach ($ids as $id) {
			$objects[] = self::get_by_id($id);
		}

		if ($sorter == 'object') {
			$objects = Util::object_sort($objects, $sort, $direction);

			if ($direction == 'desc') {
				$objects = array_reverse($objects);
			}

			$objects = array_slice($objects, ($page-1)*$limit, $limit);
		}


		return $objects;
	}

	/**
	 * Count the users
	 *
	 * @access public
	 * @param array $extra_conditions
	 * @param array $extra_joins
	 * @return int $count
	 */
	public static function count($extra_conditions = [], $extra_joins = []) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$where = self::get_search_where($extra_conditions, $extra_joins);
		$joins = self::trait_get_link_tables();

		$sql  = 'SELECT COUNT(DISTINCT(' . $table . '.id)) ';
		$sql .= 'FROM `' . $table . '` ';

		foreach ($joins as $join) {
			$sql .= 'LEFT OUTER JOIN `' . $join . '` on `' . $table . '`.' . $join . '_id = ' . $join . '.id ';
		}

		foreach ($extra_joins as $extra_join) {
			$sql .= 'LEFT OUTER JOIN `' . $extra_join[0] . '` on `' . $extra_join[0] . '`.' . $extra_join[1] . ' = ' . $extra_join[2] . "\n";
		}

		if (isset(self::$object_text_fields) AND count(self::$object_text_fields) > 0) {
			$sql .= 'LEFT OUTER JOIN object_text on object_text.classname LIKE "' . get_class() . '%" AND object_text.object_id=' . $table . '.id '  . "\n";
		}

		$sql .= 'WHERE 1 ' . $where;
		$count = $db->getOne($sql);

		return $count;
	}
}
