<?php
/**
 * Database migration class
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Migration_20180404_135343_Invoice_reminder_email extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$setting = new Setting();
		$setting->name = 'invoice_reminder_email_window';
		$setting->value = 7;
		$setting->save();
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
