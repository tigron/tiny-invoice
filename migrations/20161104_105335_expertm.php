<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20161104_105335_Expertm extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE `customer_contact_export` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `language_id` int(11) NOT NULL,
			  `firstname` varchar(64) CHARACTER SET utf8 NOT NULL,
			  `lastname` varchar(64) CHARACTER SET utf8 NOT NULL,
			  `company` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
			  `phone` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
			  `mobile` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
			  `fax` varchar(32) CHARACTER SET utf8 NOT NULL DEFAULT '',
			  `email` varchar(128) CHARACTER SET utf8 NOT NULL DEFAULT '',
			  `street` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
			  `housenumber` varchar(32) CHARACTER SET utf8 NOT NULL,
			  `city` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
			  `zipcode` varchar(10) CHARACTER SET utf8 NOT NULL,
			  `state` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			  `country_id` int(11) NOT NULL DEFAULT '0',
			  `vat` varchar(20) CHARACTER SET utf8 NOT NULL DEFAULT '',
			  `active` tinyint(4) NOT NULL,
			  `vat_bound` tinyint(4) DEFAULT NULL,
			  `created` datetime NOT NULL,
			  `updated` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `country_id` (`country_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		", []);

		$db->query("
			ALTER TABLE `customer_contact`
			ADD `customer_contact_export_id` int NULL;
		", []);


		$ids = $db->get_column('SELECT id FROM customer_contact', []);

		foreach ($ids as $id) {
			$customer_contact = Customer_Contact::get_by_id($id);
			$customer_contact->save(false);
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
