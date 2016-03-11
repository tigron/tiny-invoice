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
		$db = Database::get();
		$rows = $db->get_all('	SELECT DISTINCT i.customer_contact_id
								FROM invoice AS i
								WHERE 1
								AND	i.paid = 0
								AND i.send_reminder_mail = 1
								AND DATEDIFF(NOW(), i.expiration_date) > 7');
		foreach ($rows as $row) {
			$this->send_customer_reminder($row['customer_contact_id']);
		}

		$this->schedule('7 days');
	}

	/**
	 * Send customer reminder
	 *
	 * @access private
	 * @param int $customer_contact_id
	 */
	private function send_customer_reminder($customer_contact_id) {
		$db = Database::get();
		$company_info = [
			'company' => Setting::get('company'),
			'email' => Setting::get('email')
		];

		$invoice_rows = $db->get_all('	SELECT i.*
										FROM invoice AS i
										WHERE 1
										AND i.paid = 0
										AND i.send_reminder_mail = 1
										AND i.expiration_date < NOW()
										AND i.customer_contact_id = ?', [ $customer_contact_id ]);
		$customer_contact = Customer_Contact::get_by_id($customer_contact_id);

		$email = new Email('invoice_reminder');
		$email->add_to($customer_contact->email, $customer_contact->firstname . ' ' . $customer_contact->lastname);
		$email->set_sender($company_info['email'], $company_info['company']);

		$invoices = [];
		foreach ($invoice_rows as $invoice_row) {
			$invoice = Invoice::get_by_id($invoice_row['id']);
			$email->add_file($invoice->file);

			$invoices[] = $invoice;
		}
		$email->assign('invoices', $invoices);

		echo 'sending reminder to user: ' . $customer_contact->firstname . ' ' . $customer_contact->lastname . ' (' . $customer_contact->email . ') ' . "\n";
		$email->send();
	}
}
