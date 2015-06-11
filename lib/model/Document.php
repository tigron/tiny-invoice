<?php
/**
 * Document class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Document {
	use Model, Get, Save, Page;
	use Delete {
		delete as trait_delete;
	}

	/**
	 * Validate document data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = array()) {
		$required_fields = array('title', 'file_id');
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Save the object
	 *
	 * @access public
	 */
	public function save($validate = true) {
		if (isset($this->dirty_fields['file_id'])) {
			$generate_preview = true;
		} else {
			$generate_preview = false;
		}

		$table = self::trait_get_database_table();
		$db = self::trait_get_database();

		if (!isset($this->id) OR $this->id === null) {
			$mode = MDB2_AUTOQUERY_INSERT;
			if (!isset($this->details['created'])) {
				$this->details['created'] = date('Y-m-d H:i:s');
			}
			$where = false;
		} else {
			$mode = MDB2_AUTOQUERY_UPDATE;
			$this->details['updated'] = date('Y-m-d H:i:s');
			$where = 'id=' . $db->quote($this->id);
		}

		$db->autoExecute($table, $this->details, $mode, $where);

		if ($mode === MDB2_AUTOQUERY_INSERT) {
			$this->id = $db->getOne('SELECT LAST_INSERT_ID();');
		}

		$this->get_details();

		if ($generate_preview) {
			$this->create_preview();
		}
	}

	/**
	 * Get document tags
	 *
	 * @access public
	 * @return array $document_tags
	 */
	public function get_document_tags() {
		return Document_Tag::get_by_document($this);
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$this->file->delete();

		$document_tags = Document_Tag::get_by_document($this);
		foreach ($document_tags as $document_tag) {
			$document_tag->delete();
		}

		if (!is_null($this->preview_file_id)) {
			$file = File::get_by_id($this->preview_file_id);
			$file->delete();
		}

		$this->trait_delete();
	}

	/**
	 * Create preview
	 *
	 * @access public
	 */
	public function create_preview() {
		if (!is_null($this->preview_file_id)) {
			$file = File::get_by_id($this->preview_file_id);
			$file->delete();
			$this->preview_file_id = NULL;
			$this->save();
		}

		if (!$this->file->is_pdf()) {
			return;
		}

		system('/usr/bin/convert ' . $this->file->get_path() . '[0] ' . TMP_PATH . '/preview.jpg');
		$file = File_Store::store(str_replace('pdf', 'jpg', $this->file->name), file_get_contents(TMP_PATH . '/preview.jpg'));
		$this->preview_file_id = $file->id;
		$this->save();
		unlink(TMP_PATH . '/preview.jpg');
	}

}
