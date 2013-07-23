<?php
/**
 * Log class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Log {
	use Model, Get, Delete, Save;

	/**
	 * Get content
	 */
	public function get_content() {
		try {
			$by = ' (by ' . $this->user->username . ')';
		} catch (Exception $e) {
			$by = '';
		}

		return $this->content . $by ;
	}

	/**
	 * Get by Object
	 *
	 * @access public
	 * @param object $object
	 */
	public static function get_by_object($object) {
		$db = Database::Get();
		$classname = get_class($object);
		$ids = $db->getCol('SELECT id FROM log WHERE classname=? AND object_id=? ORDER BY id DESC LIMIT 50', array($classname, $object->id));

		$logs = array();
		foreach ($ids as $id) {
			$logs[] = Log::get_by_id($id);
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

		$log = new Log();

		try {
			$user = User::Get();
			$log->user_id = $user->id;
		} catch (Exception $e) {
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
