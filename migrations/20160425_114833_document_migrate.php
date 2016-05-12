<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20160425_114833_Document_migrate extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

//		$db->query("ALTER TABLE `document` ADD `updated` datetime NULL;");
		$db->query('UPDATE document SET classname = ?', [ 'Document' ]);

		$ids = $db->get_column('SELECT id FROM document');

		foreach ($ids as $id) {
			$updated = null;
			$row = $db->get_row('SELECT * FROM purchase WHERE document_id=?', [ $id ]);
			if ($row === null) {
				continue;
			}
			$document = Document::get_by_id($id);
			$document = $document->change_classname('Document_Incoming_Invoice');

			$document->supplier_id = $row['supplier_id'];
			$document->price_incl = $row['price_incl'];
			$document->price_excl = $row['price_excl'];
			$document->paid = $row['paid'];
			$document->date = $row['date'];
			$document->expiration_date = $row['expiration_date'];
			$updated = $row['updated'];
			$document->save();
			$db->query('UPDATE document SET updated = ? WHERE id = ?', [ $updated, $document->id ]);
		}

		$db->query('DROP TABLE `purchase`;');
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
