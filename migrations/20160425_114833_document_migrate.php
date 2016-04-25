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
		
		$db->query("ALTER TABLE `document` ADD `updated` datetime NULL;");
		$db->query('UPDATE document SET classname = ?', [ 'Document' ]);

		$ids = $db->get_column('SELECT id FROM document');
		foreach ($ids as $id) {
			$updated = null;
			$document = Document::get_by_id($id);
			if ($document->is_purchase() === true) {
				$document = $document->change_classname('Document_Incoming_Invoice');

				$purchase = Purchase::get_by_document($document);
				$document->supplier_id = $purchase->supplier_id;
				$document->price_incl = $purchase->price_incl;
				$document->price_excl = $purchase->price_excl;
				$document->paid = $purchase->paid;
				$document->date = $purchase->date;
				$document->expiration_date = $purchase->expiration_date;
				$updated = $purchase->updated;

				$document->save();
			}

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
