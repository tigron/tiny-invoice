<?php
/**
 * File class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/base/File/Store.php';

class File {
	use Model, Save, Get;

	/**
	 * Get information related to this object
	 *
	 * @param bool $exclude_content
	 * @return array An array containing the information
	 */
	public function get_info($exclude_content = false) {
		$info = [
			'id' => $this->id,
			'name' => $this->name,
			'mime_type' => $this->mime_type,
			'size' => $this->size,
			'created' => $this->created,
			'human_size' => $this->get_human_size(),
		];

		if ($exclude_content === false) {
			$info['content'] = base64_encode($this->get_contents());
		}

		return $info;
	}

	/**
	 * Is this a picture
	 *
	 * @access public
	 * @return bool $is_picture
	 */
	public function is_picture() {
		$mime_types = array(
			'image/jpeg',
			'image/jpg',
			'image/png',
			'image/gif',
			'image/tiff',
			'image/svg+xml',
		);

		if (in_array($this->mime_type, $mime_types)) {
			return true;
		}

		return false;
	}

	/**
	 * is pdf
	 *
	 * @access public
	 * @return bool $is_pdf
	 */
	public function is_pdf() {
		if ($this->mime_type == 'application/pdf') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Copy
	 *
	 * @access public
	 * @return File $file
	 */
	public function copy() {
		$file = File_Store::store($this->name, $this->get_contents());
		return $file;
	}

	/**
	 * Delete a file
	 *
	 * @access public
	 */
	public function delete() {
		File_Store::delete_file($this);
		$db = Database::Get();
		$db->query('DELETE FROM file WHERE id=?', array($this->id));
	}

	/**
	* Set expiration date
	*
	* @access public
	*/
	public function expire($delay = '+2 hours') {
		$this->expiration_date = date('Y-m-d H:i:s', strtotime($delay));
		$this->save();
	}

	/**
	* Clear expiration mark
	*
	* @access public
	*/
	public function cancel_expiration() {
		$this->expiration_date = NULL;
		$this->save();
	}

	/**
	 * Get expired files
	 *
	 * @access public
	 * @return array File $items
	 */
	public static function get_expired() {
		$db = Database::Get();
		$ids = $db->getCol('SELECT id FROM file WHERE expiration_date IS NOT NULL AND expiration_date < NOW()');

		$items = [];
		foreach ($ids as $id) {
			$items[] = File::get_by_id($id);
		}

		return $items;
	}

	/**
	 * Get a human readable version of the size
	 *
	 * @access public
	 */
	public function get_human_size() {
		return Util::file_humanize_size($this->size);
	}

	/**
	 * Get content of the file
	 *
	 * @access public
	 */
	public function get_contents() {
		return file_get_contents($this->get_path());
	}

	/**
	 * Get path
	 *
	 * @access public
	 * @return string $path
	 */
	public function get_path() {
		return File_Store::get_path($this);
	}

	/**
	 * Send this file as a download to the client
	 *
	 * @access public
	 */
	public function client_download() {
		header('Content-type: ' . $this->details['mime_type']);
		header('Content-Disposition: attachment; filename="'.$this->details['name'].'"');
		readfile($this->get_path());
		exit();
	}

	/**
	 * Send this file inline to the client
	 *
	 * @access public
	 */
	public function client_inline() {
		header('Content-type: ' . $this->details['mime_type']);
		header('Content-Disposition: inline; filename="'.$this->details['name'].'"');
		readfile($this->get_path());
		exit();
	}

	/**
	 * Get File by id
	 *
	 * @access public
	 * @param int $id
	 * @return File $file
	 */
	public static function get_by_id($id) {
		$file = new File($id);
		if ($file->is_picture()) {
			return Picture::get_by_id($id);
		} else {
			return $file;
		}
	}
}
