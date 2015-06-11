<?php
/**
 * trait: Delete
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 * @author David Vandemaele <david@tigron.be>
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

		if (isset(self::$object_text_fields)) {
			$object_texts = Object_Text::get_by_object($this);
			foreach ($object_texts as $object_text) {
				$object_text->delete();
			}
		}

		if (property_exists(get_class(), 'class_configuration') AND isset(self::$class_configuration['soft_delete']) AND self::$class_configuration['soft_delete'] === TRUE) {
			$this->deleted = date('YmdHis');
			$this->save();
		} else {
			$db->query('DELETE FROM ' . $table . ' WHERE id=?', array($this->id));
		}
	}

	public function restore() {
		if (property_exists(get_class(), 'class_configuration') AND isset(self::$class_configuration['soft_delete']) AND self::$class_configuration['soft_delete'] === TRUE) {
			$this->deleted = '0000-00-00 00:00:00';
			$this->save();
		} else {
			throw new Exception('This object cannot be restored');
		}
	}
}
