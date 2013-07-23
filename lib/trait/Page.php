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
	 */
	private static function get_search_where($extra_conditions = array()) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();

		$definition = Util::get_table_definition($table, $db);

		// Cleanup 'extra_conditions'
		foreach ($extra_conditions as $key => $value) {
			if ($key == '%search%') {
				continue;
			}

			$exists = false;
			foreach ($definition as $field_array) {
				if ($field_array['field'] == $key) {
					$exists = true;
				}
			}

			if (!$exists) {
				unset($extra_conditions[$key]);
			}
		}

		$where = '';

		foreach ($extra_conditions as $key => $value) {
			if ($key != '%search%') {
				if (!is_array($extra_conditions[$key])) {
					$where .= 'AND ' . $db->quoteidentifier($key) . ' = ' . $db->quote($value) . ' ';
				} else {
					$where .= 'AND ' . $db->quoteidentifier($key) . ' ' . $value[0] . ' ' . $db->quote($value[1]) . ' ';
				}
			}
		}

		if (isset($extra_conditions['%search%'])) {
			$iteration = 0;
			foreach ($definition as $field_array) {
				if ($iteration == 0) {
					$where .= 'AND (0 ';
				}

				$where .= 'OR ' . $field_array['field'] . " LIKE '%"  . $extra_conditions['%search%'] . "%' ";
				$iteration++;
			}

			if (count($definition) > 0) {
				$where .= ') ';
			}
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
	 */
	public static function get_paged($sort = 1, $direction = 'ASC', $page = 1, $extra_conditions = array(), $all = false) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$where = self::get_search_where($extra_conditions);
		$config = Config::Get();

		if (!$all) {
			$limit = $config->items_per_page;
		} else {
			$limit = 1000;
		}

		if ($page < 1) {
			$page = 1;
		}

        if ($direction != 'ASC') {
                $direction = 'DESC';
        }

		$sql = 'SELECT DISTINCT(id)
		        FROM `' . $table . '`
		        WHERE 1 ' . $where . '
		        ORDER BY ' . $sort . ' ' . $direction . '
		        LIMIT ' . ($page-1)*$limit . ', ' . $limit;

		$ids = $db->getCol($sql);
		$objects = array();
		foreach ($ids as $id) {
			$objects[] = self::get_by_id($id);
		}

		return $objects;
	}

	/**
	 * Count the users
	 *
	 * @access public
	 * @param array $extra_conditions
	 * @return int $count
	 */
	public static function count($extra_conditions = array()) {
		$db = self::trait_get_database();
		$table = self::trait_get_database_table();
		$where = self::get_search_where($extra_conditions);

		$sql = 'SELECT COUNT(DISTINCT(id))
		        FROM `' . $table . '`
		        WHERE 1 ' . $where;

		$count = $db->getOne($sql);

		return $count;
	}
}
