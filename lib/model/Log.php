<?php
/**
 * Log class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Log {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * Get content
	 *
	 * @access public
	 */
	public function get_content() {
		try {
			$by = ' (by ' . $this->user->username . ')';
		} catch (\Exception $e) {
			$by = '';
		}

		return $this->content . $by ;
	}

	/**
	 * Get by Object
	 *
	 * @access public
	 * @param mixed $object
	 */
	public static function get_by_object($object) {
		$db = Database::get();
		$classname = get_class($object);
		$ids = $db->get_col('SELECT id FROM log WHERE classname=? AND object_id=? ORDER BY id DESC LIMIT 50', [$classname, $object->id]);

		$logs = [];
		foreach ($ids as $id) {
			$logs[] = self::get_by_id($id);
		}

		return $logs;
	}

	/**
	 * Create a log object
	 *
	 * @access public
	 * @param string $action
	 * @param object $object
	 */
	public static function create($action, $object = null) {
		// what class is it
		$classname = '';
		if (!is_null($object)) {
			$classname = strtolower(get_class($object));
		}

		$log = new self();

		try {
			$user = User::get();
			$log->user_id = $user->id;
		} catch (\Exception $e) {
			$log->user_id = 0;
		}

		if ($action == 'add') {
			$content = ucfirst($classname) . ' created';
		} elseif ($action == 'edit') {
			$content = ucfirst($classname) . ' edited';
		} else {
			$content = ucfirst($action);
		}

		$log->classname = $classname;
		$log->object_id = !is_null($object) ? $object->id : 0;
		$log->content = $content;
		$log->save();

		return $log;
	}
}

