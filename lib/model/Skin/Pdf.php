<?php
/**
 * Skin_Pdf class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Skin_Pdf {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * synchronize skins
	 *
	 * @access public
	 */
	public function synchronize() {
		$pdf_path = dirname(__FILE__) . '/../../../store/pdf';
		$content = scandir($pdf_path);
		foreach ($content as $key => $value) {
			if ($value[0] == '.') {
				unset($content[$key]);
			}

			if (!is_dir($pdf_path . '/' . $value)) {
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
			if (file_exists($pdf_path . '/' . $path . '/meta')) {
				$skin->description = file_get_contents($pdf_path . '/' . $path . '/meta');
			} else {
				$skin->description = $path;
			}
			$skin->save();
		}
	}

	/**
	 * Get by path
	 *
	 * @access public
	 * @param string $path
	 * @return Skin_Pdf $skin_pdf
	 */
	public static function get_by_path($path) {
		$db = Database::Get();
		$id = $db->get_one('SELECT id FROM skin_pdf WHERE path=?', [ $path ]);
		if ($id === null) {
			throw new Exception('Skin pdf with path ' . $path . ' not found');
		}
		return Skin_Pdf::get_by_id($id);
	}

}
