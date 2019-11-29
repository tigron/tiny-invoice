<?php
/**
 * Document class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Document {
	use \Skeleton\Object\Model {
		__get as trait_get;
	}
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Uuid;
	use \Skeleton\Object\Save {
		save as trait_save;
	}
	use \Skeleton\Pager\Page;
	use \Skeleton\Object\Delete {
		delete as trait_delete;
	}

	/**
	 * Class configuration
	 * @var array $class_configuration
	 */
	private static $class_configuration = [
		'child_classname_field' => 'classname'
	];

	/**
	 * Get key
	 *
	 * @access public
	 * @param  string $key
	 * @return mixed
	 */
	public function __get($key) {
		if ($key == 'preview_file') {
			return File::get_by_id($this->preview_file_id);
		}

		return $this->trait_get($key);
	}

	/**
	 * Validate document data
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors) {
		$errors = [];
		$required_fields = [ 'file_id', 'title' ];
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
	 * Change classname
	 *
	 * @access public
	 * @param string $classname
	 */
	public function change_classname($classname) {
		if ($classname == $this->classname) {
			return $this;
		}
		$db = Database::get();
		$db->query('UPDATE document SET classname=? WHERE id=?', [ $classname, $this->id ]);
		return self::get_by_id($this->id);
	}

	/**
	 * Save the object
	 *
	 * @access public
	 */
	public function save($validate = true) {
		if (isset($this->dirty_fields['file_id']) OR $this->id === null) {
			$generate_preview = true;
		} else {
			$generate_preview = false;
		}

		$mode = 'update';
		if (!isset($this->id)) {
			$mode = 'insert';
		}

		$this->trait_save($validate);

		if ($mode == 'insert') {
			Log::create('add', $this);
		}

		if ($generate_preview) {
			$this->create_preview();
		}
	}

	/**
	 * Is available for API
	 *
	 * @access public
	 * @return bool $available
	 */
	public function available_for_api() {
		try {
			$public = Setting::get_by_name('api_public_documents')->value;
			if ($public) {
				return true;
			}
		} catch (Exception $e) { };

		try {
			$tag_ids = Setting::get_by_name('api_document_tag_ids')->value;
		} catch (Exception $e) {
			$tag_ids = '';
		}
		$tag_ids = explode(',', $tag_ids);
		$selected_tags = [];
		foreach ($tag_ids as $tag_id) {
			try {
				$selected_tags[] = Tag::get_by_id($tag_id);
			} catch (Exception $e) { }
		}

		foreach ($this->get_tags() as $tag) {
			foreach ($selected_tags as $selected_tag) {
				if ($tag->id == $selected_tag->id) {
					return true;
				}
			}
		}
		return false;
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
	 * Get tags
	 *
	 * @access public
	 * @return array Tag $items
	 */
	public function get_tags() {
		$tags = [];
		$document_tags = $this->get_document_tags();
		foreach ($document_tags as $document_tag) {
			$tags[] = $document_tag->tag;
		}

		return $tags;
	}

	/**
	 * Check if document is linked with tag
	 *
	 * @access public
	 * @param Tag $tag
	 * @return bool $has_tag
	 */
	public function has_tag(Tag $tag) {
		$document_tags = $this->get_document_tags();
		foreach ($document_tags as $document_tag) {
			if ($document_tag->tag_id == $tag->id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Link document with tag
	 *
	 * @access public
	 * @param Tag $tag
	 */
	public function add_tag(Tag $tag) {
		if ($this->has_tag($tag)) {
			return;
		}

		$document_tag = new Document_Tag();
		$document_tag->document_id = $this->id;
		$document_tag->tag_id = $tag->id;
		$document_tag->save();
	}

	/**
	 * remove tags
	 *
	 * @access public
	 */
	public function remove_tags() {
		$document_tags = $this->get_document_tags();
		foreach ($document_tags as $document_tag) {
			$document_tag->delete();
		}
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$extractor_exists = false;
		try {
			$extractor_pdf = Extractor_Pdf::get_by_document($this);
			$extractor_exists = true;
		} catch (Exception $e) { }

		if ($extractor_exists) {
			throw new Exception('Cannot delete document. There is an extractor based on this document');
		}

		$document_tags = Document_Tag::get_by_document($this);
		foreach ($document_tags as $document_tag) {
			$document_tag->delete();
		}

		$logs = Log::get_by_object($this);
		foreach ($logs as $log) {
			$log->delete();
		}

		if (!is_null($this->preview_file_id)) {
			try {
				$file = File::get_by_id($this->preview_file_id);
				$file->delete();
			} catch (Exception $e) { }
		}

		try {
			$this->file->delete();
		} catch (Exception $e) { }

		$this->trait_delete();
	}

	/**
	 * Get preview
	 *
	 * @access public
	 * @return Picture $picture
	 */
	public function get_preview() {
		if (empty($this->details['preview_file_id'])) {
			$this->create_preview();
		}
		return File::get_by_id($this->preview_file_id);
	}

	/**
	 * Create preview
	 *
	 * @access public
	 */
	public function create_preview() {
		if ($this->file_id == 0) {
			return;
		}
		if (!is_null($this->preview_file_id)) {
			$file = File::get_by_id($this->preview_file_id);
			$file->delete();
			$this->preview_file_id = NULL;
			$this->save();
		}

		if (!$this->file->is_pdf()) {
			return;
		}
		if (!file_exists(\Skeleton\File\Picture\Config::$tmp_dir)) {
			mkdir(\Skeleton\File\Picture\Config::$tmp_dir, 0755, true);
		}

		system('/usr/bin/convert -density 150 -background white -alpha remove -resize 800 ' . $this->file->get_path() . '[0] ' . \Skeleton\File\Picture\Config::$tmp_dir . 'preview.jpg');
		if (file_exists(\Skeleton\File\Picture\Config::$tmp_dir . 'preview.jpg')) {
			$file = File::store(str_replace('pdf', 'jpg', $this->file->name), file_get_contents(\Skeleton\File\Picture\Config::$tmp_dir . 'preview.jpg'));
			$this->preview_file_id = $file->id;
			$this->save(false);
			unlink(\Skeleton\File\Picture\Config::$tmp_dir . 'preview.jpg');
		}
	}

	/**
	 * Get by uuid
	 *
	 * @access public
	 * @param string $uuid
	 * @return Document $document
	 */
	public static function get_by_uuid($uuid) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM document WHERE uuid=?', [ $uuid ]);
		return self::get_by_id($id);
	}
}
