<?php
/**
 * Transaction_Reminder_Invoice
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Transaction_Reminder_Invoice extends Transaction {

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

		$grouped = [];
		$remindable_invoices = Invoice::get_remindable();
		foreach ($remindable_invoices as $remindable_invoice) {
			if (!isset($grouped[$remindable_invoice->customer_contact_id])) {
				$grouped[$remindable_invoice->customer_contact_id] = [];
			}
			$grouped[$remindable_invoice->customer_contact_id][] = $remindable_invoice;
		}

		foreach ($grouped as $customer_contact_id => $invoices) {
			$customer_contact = Customer_Contact::get_by_id($customer_contact_id);

			$email = new Email('invoice_reminder', $customer_contact->language);
			$email->add_to($customer_contact->email, $customer_contact->firstname . ' ' . $customer_contact->lastname);
			$email->set_sender($company_info['email'], $company_info['company']);
			$email->assign('invoices', $invoices);
			$email->assign('customer_contact', $customer_contact);
			foreach ($invoices as $invoice) {
				$email->add_attachment($invoice->get_pdf());
			}

			echo 'sending reminder to customer: ' . $customer_contact->firstname . ' ' . $customer_contact->lastname . ' (' . $customer_contact->email . ') ' . "\n";
			$email->send();
		}

		$this->schedule('1 day');
	}

}
