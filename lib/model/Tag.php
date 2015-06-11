<?php
/**
 * Tag class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Tag {
	use Model, Get, Save, Page;
	use Delete {
		delete as trait_delete;
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$document_tags = Document_Tag::get_by_tag($this);
		foreach ($document_tags as $document_tag) {
			$document_tag->delete();
		}

		$this->trait_delete();
	}

	/**
	 * Validate a tag object
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = []) {
		$required_fields = [ 'name', 'identifier' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		try {
			$tag = Tag::get_by_identifier($this->details['identifier']);
			if (isset($this->id) AND $tag->id != $this->id) {
				$errors['identifier'] = 'already_exists';
			}
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
		$table = self::trait_get_database_table();
		$db = self::trait_get_database();
		$id = $db->getOne('SELECT id FROM ' . $table . ' WHERE identifier = ?', [ $identifier ]);

		if ($id === null) {
			throw new Exception('Tag not found');
		}

		return self::get_by_id($id);
	}

}