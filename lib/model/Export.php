<?php
/**
 * Export class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

abstract class Export {
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Pager\Page;
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Delete {
		delete as trait_delete;
	}

	public function __construct($id = null) {
		$this->classname = get_class($this);
		if ($id !== null) {
			$this->id = $id;
			$this->get_details();
		}
	}

	/**
	 * Delete export
	 *
	 * @access public
	 */
	public function delete() {
		if (!is_null($this->file_id)) {
			$this->file->delete();
		}

		$this->trait_delete();
	}

	/**
	 * Get data (decoded)
	 *
	 * @access public
	 * @return array
	 */
	public function get_data() {
		return json_decode($this->data, true);
	}

	/**
	 * Run
	 *
	 * @access public
	 */
	abstract public function run();

	/**
	 * Get by id
	 *
	 * @access public
	 * @param int $id
	 * @return Export $export
	 */
	public static function get_by_id($id) {
		$db = Database::get();
		$classname = $db->get_one('SELECT classname FROM export WHERE id=?', [ $id ]);
		return new $classname($id);
	}


}
