<?php
/**
 * trait: Save
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 * @author David Vandemaele <david.vandemaele@tigron.be>
 */

trait Slug {

	/**
	 * Generate a slug
	 *
	 * @access private
	 */
	private function generate_slug($append = 0) {
		if (isset($this->details['name'])) {
			$name = $this->details['name'];
		} elseif (isset(self::$object_text_fields) AND in_array('name', self::$object_text_fields)) {
			$language = Language::get_default();
			$property = 'text_' . $language->name_short . '_name';
			$name = $this->$property;
		} else {
			throw new Exception('No base found to generate slug');
		}

		$slug = strtolower($name);
		$slug = mb_convert_encoding($slug, 'ISO-8859-1', 'UTF-8');
		$slug = str_replace('.', '-', $slug);
		$slug = str_replace(' ', '-', $slug);
		$slug = str_replace('/', '-', $slug);
		$slug = str_replace('?', '-', $slug);
		$slug = str_replace('&', 'and', $slug);

		if ($append != 0) {
			$slug .= '-' . $append;
		}

		$slug_exist = false;
		try {
			$object = self::get_by_slug($slug);
			if ($this->id === null) {
				$slug_exist = true;
			}

			if ($this->id != $object->id) {
				$slug_exist = true;
			}

		} catch (Exception $e) {
			$slug_exist = false;
		}

		if ($slug_exist) {
			++$append;
			return $this->generate_slug($append);
		}
		return $slug;
	}

	/**
	 * get by slug
	 *
	 * @access public
	 * @param string $name
	 * @return Object $object
	 */
	public static function get_by_slug($slug) {
		$table = self::trait_get_database_table();
		$fields = Util::mysql_get_table_fields($table);
		$db = Database::Get();

		$id = $db->getOne('SELECT id FROM ' . $db->quoteIdentifier($table) . ' WHERE slug=?', array($slug));
		if ($id === null) {
			throw new Exception('Object not found');
		}
		return self::get_by_id($id);
	}
}
