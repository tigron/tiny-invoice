<?php
/**
 * Tag class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Tag {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete {
		delete as trait_delete;	
	}
	use \Skeleton\Pager\Page;
	
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
		$required_fields = [ 'name' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		try {
			$tag = Tag::get_by_name($this->details['name']);
			if (isset($this->id) AND $tag->id != $this->id) {
				$errors['name'] = 'already_exists';
			}
		} catch (Exception $e) {}

		if (count($errors) == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get by name
	 *
	 * @access public
	 * @param string $name
	 * @return Tag $tag
	 */
	public static function get_by_name($name) {
		$table = self::trait_get_database_table();
		$db = self::trait_get_database();
		$id = $db->getOne('SELECT id FROM tag WHERE name = ?', [ $name ]);

		if ($id === null) {
			throw new Exception('Tag not found');
		}

		return self::get_by_id($id);
	}

}
