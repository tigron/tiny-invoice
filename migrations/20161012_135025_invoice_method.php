<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161012_135025_Invoice_method extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE `invoice_method` (
			  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  `name` varchar(128) NOT NULL,
			  `classname` varchar(128) NOT NULL
			) ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';
		", []);

		$db->query("
			INSERT INTO `invoice_method` (`name`, `classname`)
			VALUES ('E-Mail', 'Invoice_Method_Mail');
		", []);

		$db->query("
			INSERT INTO `invoice_method` (`name`, `classname`)
			VALUES ('Click & Post', 'Invoice_Method_Clickpost');
		", []);

		$db->query("
			ALTER TABLE `customer_contact`
			ADD `invoice_method_id` int NOT NULL AFTER `customer_id`;
		", []);

		$db->query("
			ALTER TABLE `invoice_method`
			ADD `icon` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL;
		", []);

		$db->query("
			UPDATE `invoice_method` SET
			`id` = '1',
			`name` = 'Mail',
			`classname` = 'Invoice_Method_Mail',
			`icon` = 'fa fa-file-pdf-o'
			WHERE `id` = '1';
		", []);

		$db->query("
			UPDATE `invoice_method` SET
			`id` = '2',
			`name` = 'Click & Post',
			`classname` = 'Invoice_Method_Clickpost',
			`icon` = 'fa fa-envelope-o'
			WHERE `id` = '2';
		", []);
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
