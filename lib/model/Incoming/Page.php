<?php
/**
 * Incoming_Page class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Incoming_Page {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Pager\Page;
	use \Skeleton\Object\Delete {
		delete as trait_delete;
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		try {
			$preview = \Skeleton\File\File::get_by_id($this->preview_file_id);
			$preview->delete();
		} catch (Exception $e) { }

		try {
			$this->file->delete();
		} catch (Exception $e) { }

		$this->trait_delete();
	}

	/**
	 * Create preview
	 *
	 * @access public
	 */
	public function create_preview() {
		system('/usr/bin/convert -density 150 -background white -alpha remove -resize 600 ' . $this->file->get_path() . '[0] ' . \Skeleton\File\Picture\Config::$tmp_dir . '/preview.jpg');
		$file = File::store(str_replace('pdf', 'jpg', $this->file->name), file_get_contents(\Skeleton\File\Picture\Config::$tmp_dir . '/preview.jpg'));
		$this->preview_file_id = $file->id;
		$this->save();
		unlink(\Skeleton\File\Picture\Config::$tmp_dir . '/preview.jpg');
	}

	/**
	 * Get by incoming
	 *
	 * @access public
	 * @param Incoming $incoming
	 * @return array $incoming_pages
	 */
	public static function get_by_incoming(Incoming $incoming) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM incoming_page WHERE incoming_id=?', [ $incoming->id ]);
		$pages = [];
		foreach ($ids as $id) {
			$pages[] = self::get_by_id($id);
		}
		return $pages;
	}

}
