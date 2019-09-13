<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20190726_170521_Payment_file extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("
			CREATE TABLE `payment_list` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `bank_account_id` int(11) NOT NULL,
			  `export_id` int(11) DEFAULT NULL,
			  `created` int(11) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `bank_account_id` (`bank_account_id`),
			  KEY `export_id` (`export_id`),
			  CONSTRAINT `payment_list_ibfk_1` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_account` (`id`),
			  CONSTRAINT `payment_list_ibfk_2` FOREIGN KEY (`export_id`) REFERENCES `export` (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		", []);

		$db->query("
			CREATE TABLE `payment` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `payment_list_id` int(11) NOT NULL,
			  `document_id` int(11) NOT NULL,
			  `bank_account_number` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			  `bank_account_bic` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
			  `payment_message` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  `payment_structured_message` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
			  `amount` decimal(10,2) NOT NULL,
			  `created` datetime NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `document_id` (`document_id`),
			  KEY `payment_list_id` (`payment_list_id`),
			  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `document` (`id`),
			  CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`payment_list_id`) REFERENCES `payment_list` (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		", []);

		$db->query("
			ALTER TABLE `bank_account`
			ADD `default_for_payment` tinyint(4) NOT NULL AFTER `from_coda`;
		", []);

		try {
			$iban = Setting::get_by_name('company')->value;
			$bic = Setting::get_by_name('bic')->value;
		} catch (Exception $e) {
			$iban = null;
			$bic = null;
		}

		if ($iban !== null and $bic !== null) {
			try {
				$bank_account = Bank_Account::get_by_number(trim($iban));
			} catch (Exception $e) {
				$bank_accounts = Bank_Account::get_all();
				if (count($bank_accounts) == 0) {
					$bank_account = new Bank_Account();
					$bank_account->number = $iban;
					$bank_account->bic = $bic;
					$bank_account->name = 'Default account';
					$bank_account->save();
				}
			}
		}

		$bank_accounts = Bank_Account::get_all();
		foreach ($bank_accounts as $bank_account) {
			$bank_account->number = trim($bank_account->number);
			$bank_account->save();
		}

		if (count($bank_accounts) > 0) {
			$first_bank_account = array_shift($bank_accounts);
			$first_bank_account->default_for_payment = true;
			$first_bank_account->save();
		}

		$exports = $db->get_all('SELECT * FROM export WHERE classname=? OR classname=?', [ 'Export_Payment_Sepa', 'Export_Payment_Belfius' ]);
		foreach ($exports as $export) {
			$data = [
				'bank_account_id' => $first_bank_account->id,
				'export_id' => $export['id'],
				'created' => $export['created']
			];
			$db->insert('payment_list', $data);
			$payment_list_id = $db->get_one('SELECT LAST_INSERT_ID();');

			$data = json_decode($export['data'], true);
			$document_ids = $data['document_ids'];
			foreach ($document_ids as $document_id) {
				$data = [
					'document_id' => $document_id,
					'payment_list_id' => $payment_list_id,
				];

				try {
					$document = Document::get_by_id($document_id);
					$data['bank_account_number'] = $document->supplier->iban;
					$data['bank_account_bic'] = $document->supplier->bic;
					$data['payment_message'] = $document->payment_message;
					$data['payment_structured_message'] = $document->payment_structured_message;
					$data['amount'] = $document->price_incl;
				} catch (Exception $e) { }
				$db->insert('payment', $data);
			}
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
