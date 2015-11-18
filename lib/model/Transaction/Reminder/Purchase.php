<?php
/**
 * Transaction_Reminder_Purchase
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Transaction_Reminder_Purchase extends Transaction {

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		$company_info = [
			'company' => Setting::get('company'),
			'email' => Setting::get('email')
		];

		$purchases = Purchase::get_expiring();
		if (count($purchases) == 0) {
			echo 'No expiring purchase documents';
			$this->schedule('3 days');
			return;
		}

		$email_template = '_default/purchase_reminder';
		$setting = Setting::get('reminder_purchase_email_template');
		if (!is_null($setting) AND $setting != '') {
			$email_template = $setting;
		}

		$email = new Email($email_template, Language::get_default());
		$email->add_to($company_info['email']);
		$email->set_sender($company_info['email'], $company_info['company']);
		$email->assign('purchases', $purchases);
		$email->send();

		$this->schedule('3 days');
	}
}
