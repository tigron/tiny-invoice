<?php
/**
 * Database migration class
 *
 */

use \Skeleton\Database\Database;

class Migration_20190102_152011_Extractor_relative extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `extractor_pdf_fingerprint`
			CHANGE `x` `x` decimal(10,6) NOT NULL AFTER `extractor_pdf_id`,
			CHANGE `y` `y` decimal(10,6) NOT NULL AFTER `x`,
			CHANGE `height` `height` decimal(10,6) NOT NULL AFTER `y`,
			CHANGE `width` `width` decimal(10,6) NOT NULL AFTER `height`;
		", []);

		$ids = $db->get_column('SELECT id FROM extractor_pdf_fingerprint', []);
		foreach ($ids as $id) {
			$extractor_pdf_fingerprint = Extractor_pdf_Fingerprint::get_by_id($id);
			try {
				$preview = $extractor_pdf_fingerprint->extractor_pdf->document->get_preview();
			} catch (Exception $e) {
				continue;
			}

			$new_x = 100 / $preview->width * $extractor_pdf_fingerprint->x;
			$new_y = 100 / $preview->height * $extractor_pdf_fingerprint->y;

			$new_width = 100 / $preview->width * $extractor_pdf_fingerprint->width;
			$new_height = 100 / $preview->height * $extractor_pdf_fingerprint->height;

			$extractor_pdf_fingerprint->x = $new_x;
			$extractor_pdf_fingerprint->y = $new_y;
			$extractor_pdf_fingerprint->width = $new_width;
			$extractor_pdf_fingerprint->height = $new_height;
			$extractor_pdf_fingerprint->save();
		}

	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
