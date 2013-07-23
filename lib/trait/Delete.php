<?php
/**
 * trait: Delete
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 */

trait Delete {
	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$table = self::trait_get_database_table();
		$db = self::trait_get_database();

		$db->query('DELETE FROM ' . $table . ' WHERE id=?', array($this->id));
	}
}
