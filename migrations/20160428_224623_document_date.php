<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20160428_224623_Document_date extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query('
			ALTER TABLE `document`
			ADD `date` date NULL AFTER `preview_file_id`;
		', []);

		$db->query('
			UPDATE document
			INNER JOIN document_incoming_invoice ON document_incoming_invoice.document_id=document.id
			SET document.date = document_incoming_invoice.date
		', []);

		$db->query('
			UPDATE document SET date=created WHERE date is NULL;
		', []);

		$db->query('
			ALTER TABLE `document_incoming_invoice`
			DROP `date`;
		', []);

	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
