<?php
/**
 * File class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/base/File/Store.php';

class File {
	use Model, Save, Get, Delete;

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
	 * Get path
	 *
	 * @access public
	 * @return string $path
	 */
	public function get_path() {
		$created = strtotime($this->created);
		$path = STORE_PATH . '/file/' . date('Y', $created) . '/' . date('m', $created) . '/' . date('d', $created) . '/' . $this->unique_name;
		return $path;
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
	 * Get content of the file
	 *
	 * @access public
	 */
	public function get_contents() {
		return file_get_contents($this->get_path());
	}

	/**
	 * Get by unique_name
	 *
	 * @access public
	 * @param string $unique_name
	 * @return File $file
	 */
	public static function get_by_unique_name($name) {
		$db = Database::Get();
		$id = $db->getOne('SELECT id FROM file WHERE unique_name=?', array($name));
		if ($id === null) {
			throw new Exception('File not found');
		}

		$file = File::get_by_id($id);
		if ($file->is_picture()) {
			return Picture::get_by_id($file->id);
		} else {
			return $file;
		}
	}
}
