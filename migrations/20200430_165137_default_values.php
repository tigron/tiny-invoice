<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20200430_165137_Default_values extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$ids = $db->get_column('SELECT id FROM customer_contact WHERE language_id = 0');
		foreach ($ids as $id) {
			$customer_contact = Customer_Contact::get_by_id($id);
			$customer_contact->language = $customer_contact->customer->language;
			$customer_contact->save();
		}

		$db->query("
			ALTER TABLE `creditnote`
			CHANGE `file_id` `file_id` int(11) NULL AFTER `customer_contact_id`,
			CHANGE `price_excl` `price_excl` decimal(10,2) NULL AFTER `number`,
			CHANGE `price_incl` `price_incl` decimal(10,2) NULL AFTER `price_excl`;
		", []);

		$db->query("
			ALTER TABLE `creditnote_item`
			CHANGE `creditnote_id` `creditnote_id` int(11) NULL AFTER `id`;
		", []);

		$db->query("
			ALTER TABLE `customer`
			CHANGE `language_id` `language_id` int(11) DEFAULT 1 AFTER `uuid`,
			CHANGE `state` `state` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `zipcode`;
		", []);

		$db->query("
			ALTER TABLE `customer_contact`
			CHANGE `language_id` `language_id` int(11) DEFAULT 1 AFTER `invoice_method_id`,
			CHANGE `alias` `alias` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `language_id`,
			CHANGE `state` `state` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `zipcode`,
			CHANGE `reference` `reference` varchar(64) COLLATE 'utf8_unicode_ci' NULL AFTER `vat`;
		", []);

		$db->query("
			ALTER TABLE `customer_contact_export`
			CHANGE `language_id` `language_id` int(11) DEFAULT 1 AFTER `id`,
			CHANGE `state` `state` varchar(128) COLLATE 'utf8_unicode_ci' NULL AFTER `zipcode`;
		", []);

		$db->query("
			ALTER TABLE `document`
			CHANGE `description` `description` text COLLATE 'utf8_unicode_ci' NULL AFTER `title`;
		", []);

		$db->query("
			ALTER TABLE `document_incoming_creditnote`
			CHANGE `balanced` `balanced` tinyint(4) DEFAULT 0 AFTER `expiration_date`;
		", []);

		$db->query("
			ALTER TABLE `export`
			CHANGE `data` `data` text COLLATE 'utf8_unicode_ci' NULL AFTER `classname`;
		", []);

		$db->query("
			ALTER TABLE `extractor_bank_account_statement_transaction`
			CHANGE `bank_account_statement_transaction_id` `bank_account_statement_transaction_id` int(11) NULL AFTER `id`;
		", []);

		$db->query("
			ALTER TABLE `extractor_pdf`
			CHANGE `document_id` `document_id` int(11) NULL AFTER `id`;
		", []);

		$db->query("
			ALTER TABLE `extractor_pdf_fingerprint`
			CHANGE `value` `value` varchar(255) COLLATE 'utf8_unicode_ci' NULL AFTER `sort`;
		", []);

		$db->query("
			ALTER TABLE `invoice`
			CHANGE `file_id` `file_id` int(11) NULL AFTER `customer_contact_id`,
			CHANGE `paid` `paid` tinyint(4) DEFAULT 0 AFTER `customer_contact_id`,
			CHANGE `internal_reference` `internal_reference` varchar(64) COLLATE 'utf8_unicode_ci' NULL AFTER `reference`,
			CHANGE `price_excl` `price_excl` decimal(10,2) NULL AFTER `send_reminder_mail`,
			CHANGE `price_incl` `price_incl` decimal(10,2) NULL AFTER `price_excl`,
			CHANGE `ogm` `ogm` varchar(20) NULL AFTER `service_delivery_to_country_id`;
		", []);

		$db->query("
			ALTER TABLE `invoice_item`
			CHANGE `invoice_id` `invoice_id` int(11) DEFAULT 1 AFTER `id`,
			CHANGE `vat_rate_value` `vat_rate_value` decimal(10,2) NULL AFTER `price_incl`;
		", []);

		$db->query("
			ALTER TABLE `invoice_queue`
			CHANGE `vat_rate_value` `vat_rate_value` decimal(10,2) NULL AFTER `price`;
		", []);

		$db->query("
			ALTER TABLE `invoice_queue_recurring_group`
			CHANGE `stop_after` `stop_after` datetime NULL AFTER `next_run`,
			CHANGE `direct_invoice` `direct_invoice` tinyint(4) DEFAULT 0 AFTER `customer_contact_id`,
			CHANGE `direct_invoice_expiration_period` `direct_invoice_expiration_period` varchar(32) COLLATE 'utf8_unicode_ci' NULL AFTER `direct_invoice`,
			CHANGE `direct_invoice_send_invoice` `direct_invoice_send_invoice` tinyint(4) DEFAULT 0 AFTER `direct_invoice_expiration_period`,
			CHANGE `direct_invoice_reference` `direct_invoice_reference` varchar(64) COLLATE 'utf8_unicode_ci' NULL AFTER `direct_invoice_send_invoice`,
			CHANGE `archived` `archived` DATETIME NULL AFTER `created`;
		", []);

		$db->query("
			UPDATE `invoice_queue_recurring_group`
			SET `archived` = NULL
			WHERE `archived` = '0000-00-00 00:00:00';
		", []);

		$db->query("
			ALTER TABLE `invoice_vat`
			CHANGE `vat_rate_id` `vat_rate_id` int(11) NULL AFTER `invoice_id`;
		", []);

		$db->query("
			ALTER TABLE `log`
			CHANGE `user_id` `user_id` int(11) NULL AFTER `id`;
		", []);

		$db->query("
			ALTER TABLE `transfer`
			CHANGE `invoice_id` `invoice_id` int(11) NULL AFTER `id`,
			CHANGE `bank_account_statement_transaction_id` `bank_account_statement_transaction_id` int(11) NULL AFTER `amount`,
			CHANGE `bank_account_statement_transaction_balance_id` `bank_account_statement_transaction_balance_id` int(11) NULL AFTER `bank_account_statement_transaction_id`;
		");

		$db->query("
			ALTER TABLE `user`
			CHANGE `language_id` `language_id` int(11) NOT NULL DEFAULT 1 AFTER `id`,
			CHANGE `admin` `admin` tinyint(1) NOT NULL DEFAULT 0 AFTER `role_id`,
			CHANGE `receive_expired_invoice_overview` `receive_expired_invoice_overview` tinyint(1) NOT NULL DEFAULT 0 AFTER `admin`;
		", []);

		$db->query("
			ALTER TABLE `incoming_page`
			CHANGE `preview_file_id` `preview_file_id` int(11) NULL AFTER `file_id`;
		", []);

		$db->query("
			ALTER TABLE `invoice_queue_recurring`
			CHANGE `archived` `archived` datetime NULL AFTER `created`;
		", []);

		$db->query("
			UPDATE invoice_queue_recurring SET archived = null WHERE archived = "0000-00-00 00:00:00"
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
