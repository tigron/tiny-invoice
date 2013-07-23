<?php
/**
 * User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Tag {
	use Model, Get, Save, Delete, Page;

	/**
	 * Validate a tag object
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = array()) {
		$required_fields = array('name', 'identifier');
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		try {
			$tag = Tag::get_by_identifier($this->details['identifier']);
			$errors['identifier'] = 'already_exists';
		} catch (Exception $e) {}

		if (count($errors) == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get by identifier
	 *
	 * @access public
	 * @param string $identifier
	 * @return Tag $tag
	 */
	public static function get_by_identifier($identifier) {
		$db = Database::Get();
		$table = self::trait_get_database_table();
		$id = $db->getOne('SELECT id FROM ' . $table . ' WHERE identifier = ?', array($identifier));

		if ($id === null) {
			throw new Exception('Tag not found');
		}

		return self::get_by_id($id);
	}
}
