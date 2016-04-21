<?php
/**
 * Incoming class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Incoming {
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
			$this->file->delete();
		} catch (Exception $e) { }

		foreach ($this->get_incoming_pages() as $incoming_page) {
			$incoming_page->delete();
		}

		$this->trait_delete();
	}


	/**
	 * Get incoming_pages
	 *
	 * @access public
	 * @return array $incoming_pages
	 */
	public function get_incoming_pages() {
		return Incoming_Page::get_by_incoming($this);
	}

}
