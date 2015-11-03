<?php
/**
 * Transaction_Order
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Transaction_Mailscanner extends Transaction {

	/**
	 * Run
	 *
	 * @access private
	 */
	public function run() {
		try {
			$host = Setting::get_by_name('mailscanner_host')->value;
			$username = Setting::get_by_name('mailscanner_username')->value;
			$password = Setting::get_by_name('mailscanner_password')->value;

			try {
				$mailscanner = new Mailscanner($host, $username, $password);
				$mailscanner->run();
			} catch (Exception $e) {
				// Error while fetching
			}

			try {
				$setting = Setting::get_by_name('mailscanner_last_check');
			} catch (Exception $e) {
				$setting = new Setting();
				$setting->name = 'mailscanner_last_check';
			}
			$setting->value = date('Y-m-d H:i:s');
			$setting->save();
		} catch (Exception $e) {
		}
		$this->sleep('1 minute');

	}
}
