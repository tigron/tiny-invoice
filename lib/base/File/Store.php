<?php
/**
 * File_Store Class
 *
 * Stores and retrieves files
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 */

require_once LIB_PATH . '/model/File.php';
require_once LIB_PATH . '/model/Picture.php';

class File_Store {

	/**
	 * Private constructor
	 *
	 * @access private
	 */
	private function __construct() {}

	/**
	 * Store a file
	 *
	 * @param string $name
	 * @param string $mimetype
	 * @param mixed $content
	 * @access public
	 */
	public static function store($name, $content) {
		$file = new File();
		$file->name = $name;
		$file->save();

		$created = strtotime($file->created);
		$dir = STORE_PATH . '/file/' . date('Y', $created) . '/' . date('m', $created) . '/' . date('d', $created);

		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$unique_name = $dir . '/' . str_replace('.', '', microtime(true)) . '-' . Util::sanitize_filename($file->name);

		file_put_contents($unique_name, $content);
		$size = filesize($unique_name);
		$file->mime_type = Util::mime_type($unique_name);
		$file->unique_name = basename($unique_name);
		$file->size = filesize($unique_name);
		$file->save();

		if ($file->is_picture()) {
			$picture = new Picture();
			$picture->file_id = $file->id;
			$picture->save();
			return $picture;
		}

		return $file;
	}

	/**
	 * Upload a file
	 *
	 * @access public
	 * @param array $_FILES['file']
	 * @return File $file
	 */
	public static function upload($fileinfo) {
		$file = new File();
		$file->name = $fileinfo['name'];
		$file->save();

		$created = strtotime($file->created);
		$dir = STORE_PATH . '/file/' . date('Y', $created) . '/' . date('m', $created) . '/' . date('d', $created);

		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		$unique_name = $dir . '/' . str_replace('.', '', microtime(true)) . '-' . Util::sanitize_filename($file->name);

		if (!move_uploaded_file($fileinfo['tmp_name'], $unique_name)) {
			throw new Exception('upload failed');
		}
		$file->unique_name = basename($unique_name);
		$file->size = filesize($unique_name);
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_file($finfo, $unique_name);
		$file->mimetype = $mime_type;
		$file->save();

		if ($file->is_picture()) {
			$picture = Picture::get_by_id($file->id);
		}
		return $file;
	}

	/**
	 * Delete a file
	 *
	 * @access public
	 * @param File $file
	 */
	public static function delete_file(File $file) {
		$created = strtotime($file->created);
		$dir = STORE_PATH . '/file/' . date('Y', $created) . '/' . date('m', $created) . '/' . date('d', $created);
		unlink($dir . '/' . $file->unique_name);
	}
}
