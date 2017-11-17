<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20170712_164404_Creditnote_balance extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {

		$db = Database::get();
		$db->query('
			ALTER TABLE `document_incoming_creditnote`
			ADD `balanced` tinyint NOT NULL;
		', []);

		$ids = $db->get_column('SELECT document_id FROM document_incoming_creditnote', []);
		foreach ($ids as $id) {
			$document = Document::get_by_id($id);
			if ($document->get_balance() == 0){
				$document->balanced = true;
				$document->save(false);
			}
		}

		$db->query("
			ALTER TABLE `transfer`
			ADD `bank_account_statement_transaction_balance_id` int NOT NULL AFTER `bank_account_statement_transaction_id`;
		", []);

		$ids = $db->get_column('SELECT id FROM transfer WHERE bank_account_statement_transaction_id > 0', []);
		foreach ($ids as $id) {
			$transfer = Transfer::get_by_id($id);
			$transaction = $transfer->bank_account_statement_transaction;
			$balances = $transaction->get_bank_account_statement_transaction_balances();
			foreach ($balances as $balance) {
				$count_transfers = $db->get_one('SELECT count(*) FROM transfer WHERE bank_account_statement_transaction_balance_id=?', [ $balance->id ]);

				if ($balance->amount == $transfer->amount AND $count_transfers == 0) {
					$transfer->bank_account_statement_transaction_balance_id = $balance->id;
					$transfer->save();
				}
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
