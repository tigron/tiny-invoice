<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @author Lionel Laffineur <lionel@tigron.be>
 */


use \Skeleton\Database\Database;

class Migration_20180511_133942_Invoice_reminder extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		try {
			$invoice_reminder = Setting::get_by_name('enable_invoice_reminder');
		} catch (Exception $e) {
			$invoice_reminder = new Setting();
			$invoice_reminder->name = 'enable_invoice_reminder';
		}

		$id = $db->get_one('SELECT id FROM transaction WHERE classname=?', [ 'Reminder_Invoice' ]);
		if ($id === null) {
			$db->query("
				INSERT INTO `transaction` (`classname`, `created`, `data`, `retry_attempt`, `recurring`, `completed`, `failed`, `locked`, `frozen`, `parallel`) VALUES
				('Reminder_Invoice',	'2016-04-25 13:48:06',	'\"\"',	0,	1,	1,	0,	0,	0,	0);",
			[]);

			$invoice_reminder->value = false;
			$invoice_reminder->save();
			return;
		} else {
			$transaction = Transaction::get_by_id($id);
			if ($transaction->completed or $transaction->frozen or $transaction->failed or $transaction->locked) {
				$invoice_reminder->value = false;
				$invoice_reminder->save();
			} else {
				$invoice_reminder->value = true;
				$invoice_reminder->save();
			}

			$transaction->locked = false;
			$transaction->frozen = false;
			$transaction->completed = false;
			$transaction->failed = false;
			$transaction->save();
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
