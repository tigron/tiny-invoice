<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20230403_180743_Add_archived_column_to_bank_account_table extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("ALTER TABLE `bank_account` ADD `archived` datetime NULL;");

		$ibans = ['BE38068933096072', 'BE41068935194710', 'BE45779596245189', 'BE32833568307102'];
		$bank_accounts = Bank_Account::get_all();

		foreach($bank_accounts as $bank_account){
			if(!in_array($bank_account->number, $ibans)){
				$bank_account->archived = date('Y-m-d H:i:s');
				$bank_account->save();
			} else {
				$bank_account->archived = null;
				$bank_account->save();
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
