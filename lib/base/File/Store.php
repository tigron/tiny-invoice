<?php
/**
 * File_Store Class
 *
 * Stores and retrieves files
 *
 * @package %%PACKAGE%%
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @version $Id$
 */

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
	 * @access public
	 * @param string $name
	 * @param mixed $content
	 * @param datetime $created
	 * @return File $file
	 */
	public static function store($name, $content, $created = null) {
		$file = new File();
		$file->name = $name;
		$file->md5sum = hash('md5', $content);
		$file->save();

		if (is_null($created)) {
			$created = time();
		} else {
			$created = strtotime($created);
		}

		$file->created = date('Y-m-d H:i:s', $created);
		$file->save();

		// create directory if not exist
		$path = self::get_path($file);
		$pathinfo = pathinfo($path);
		if (!is_dir($pathinfo['dirname'])) {
			mkdir($pathinfo['dirname'], 0755, true);
		}

		// store file on disk
		file_put_contents($path, $content);

		// set mime type and size
		$file->mime_type = Util::file_mime_type($path);
		$file->size = filesize($path);

		$file->save();

		return File::get_by_id($file->id);
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
		$file->md5sum = hash('md5', file_get_contents($fileinfo['tmp_name']));
		$file->save();

		// create directory if not exist
		$path = self::get_path($file);
		$pathinfo = pathinfo($path);
		if (!is_dir($pathinfo['dirname'])) {
			mkdir($pathinfo['dirname'], 0755, true);
		}

		// store file on disk
		if (!move_uploaded_file($fileinfo['tmp_name'], $path)) {
			throw new Exception('upload failed');
		}

		// set mime type and size
		$file->mime_type = Util::file_mime_type($path);
		$file->size = filesize($path);
		$file->save();

		return File::get_by_id($file->id);
	}

	/**
	 * Delete a file
	 *
	 * @access public
	 * @param File $file
	 */
	public static function delete_file(File $file) {
		if (file_exists($file->get_path())) {
			unlink($file->get_path());
		}
	}

	/**
	 * Get the physical path of a file
	 *
	 * @param File $file
	 * @return string $path
	 */
	public static function get_path(File $file) {
		$subpath = substr(base_convert($file->md5sum, 16, 10), 0, 3);
		$subpath = implode('/', str_split($subpath)) . '/';

		$path = STORE_PATH . '/file/' . $subpath . $file->id . '-' . Util::file_sanitize_name($file->name);

		return $path;
	}
}
