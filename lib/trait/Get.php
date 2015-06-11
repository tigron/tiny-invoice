<?php
/**
 * trait: Get
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 */

trait Get {
	/**
	 * Get object info
	 *
	 * @access public
	 * @return array $details
	 */
	public function get_info() {
		return $this->details;
	}

	/**
	 * Get by id
	 *
	 * @access public
	 * @parm int $id
	 * @return object
	 */
	public static function get_by_id($id) {
		if ($id === null) {
			throw new Exception('Can not fetch ' . get_class() . ' with id null');
		}

		return new self($id);
	}

	/**
	 * Get all
	 *
	 * @access public
	 * @return array objects
	 */
	public static function get_all($sort = null, $direction = null) {
		$table = self::trait_get_database_table();
		$db = self::trait_get_database();

		$where = '';
		if (property_exists(get_class(), 'class_configuration') AND isset(self::$class_configuration['soft_delete']) AND self::$class_configuration['soft_delete'] === TRUE) {
			$where = ' AND deleted = 0';
		}
		
		if (is_null($sort)) {
			$ids = $db->getCol('SELECT id FROM ' . $table . ' WHERE 1' . $where, []);
		} else {
			if (is_null($direction)) {
				$direction = 'ASC';
			}
			$ids = $db->getCol('SELECT id FROM ' . $table . ' WHERE 1' . $where . ' ORDER BY ' . $sort . ' ' . $direction);
		}

		$objects = array();
		foreach ($ids as $id) {
			$objects[] = self::get_by_id($id);
		}

		return $objects;
	}
}

