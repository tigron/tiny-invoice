<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20160512_211831_Transfers extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			CREATE TABLE `bank_account` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `identifier` int(11) NOT NULL,
			  `name` varchar(128) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		", []);

		$db->query("
			CREATE TABLE `bank_account_statement` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `bank_account_id` int(11) NOT NULL,
			  `date` date NOT NULL,
			  `orinal_balance` decimal(10,3) NOT NULL,
			  `new_balance` decimal(10,3) NOT NULL,
			  `created` datetime NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		", []);

		$db->query("
			CREATE TABLE `bank_account_statement_parser` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `classname` varchar(128) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		", []);

		$db->query("
			CREATE TABLE `bank_account_statement_transaction` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `bank_account_statement` int(11) NOT NULL,
			  `date` date NOT NULL,
			  `valuta_date` date NOT NULL,
			  `amount` decimal(10,3) NOT NULL,
			  `message` varchar(255) NOT NULL,
			  `structured_message` varchar(128) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		", []);

		$db->query("
			CREATE TABLE `transfer` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `invoice_id` int(10) unsigned NOT NULL,
			  `type` tinyint(4) NOT NULL,
			  `amount` decimal(10,2) NOT NULL,
			  `created` datetime NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `invoice_id` (`invoice_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
