<?php
/**
 * Transaction_Reminder_Purchase
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Transaction_Reminder_Document_Incoming_Invoice extends Transaction {

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

		$incoming_invoices = Document_Incoming_Invoice::get_expiring();
		if (count($incoming_invoices) > 0) {
			$users = User::get_all();
			foreach ($users as $user) {
				if ($user->receive_expired_invoice_overview) {
					$email = new Email('incoming_invoice_reminder', Language::get_default());
					$email->add_to($user->email);
					$email->set_sender($company_info['email'], $company_info['company']);
					$email->assign('incoming_invoices', $incoming_invoices);
					$email->send();
				}
			}
		}

		$this->schedule('+1 days');
	}
}
