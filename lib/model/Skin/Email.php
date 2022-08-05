<?php
/**
 * Skin_Email class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Skin_Email {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * synchronize skins
	 *
	 * @access public
	 */
	public static function synchronize() {
		$email_path = dirname(__FILE__) . '/../../../store/email';
		$content = scandir($email_path);
		foreach ($content as $key => $value) {
			if ($value[0] == '.') {
				unset($content[$key]);
			}

			if (!is_dir($email_path . '/' . $value)) {
				unset($content[$key]);
			}
		}

		foreach ($content as $path) {
			try {
				$skin = self::get_by_path($path);
			} catch (Exception $e) {
				$skin = new self();
				$skin->path = $path;
			}
			if (file_exists($email_path . '/' . $path . '/meta')) {
				$skin->description = file_get_contents($email_path . '/' . $path . '/meta');
			} else {
				$skin->description = $path;
			}
			$skin->save();
		}

		$skins = Skin_Email::get_all();
		foreach ($skins as $skin) {
			if (!file_exists($email_path . '/' . $skin->path)) {
				$skin->delete();
			}
		}
	}

	/**
	 * Get by path
	 *
	 * @access public
	 * @param string $path
	 * @return Skin_Email $skin_email
	 */
	public static function get_by_path($path) {
		$db = Database::Get();
		$id = $db->get_one('SELECT id FROM skin_email WHERE path=?', [ $path ]);
		if ($id === null) {
			throw new Exception('Skin email with path ' . $path . ' not found');
		}
		return Skin_Email::get_by_id($id);
	}

}
