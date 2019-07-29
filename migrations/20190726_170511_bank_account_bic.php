<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20190726_170511_Bank_account_bic extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("
			ALTER TABLE `bank_account`
			CHANGE `name` `name` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `bookkeeping_account_id`,
			CHANGE `description` `description` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `name`,
			CHANGE `alias` `alias` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `description`,
			CHANGE `number` `number` varchar(128) COLLATE 'utf8_unicode_ci' NOT NULL AFTER `alias`,
			ADD `bic` varchar(32) COLLATE 'utf8_unicode_ci' NOT NULL,
			ADD `created` datetime NOT NULL AFTER `bic`;
		", []);

		$db->query("
			ALTER TABLE `bank_account`
			ADD `from_coda` tinyint NOT NULL DEFAULT '0' AFTER `bic`;
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
