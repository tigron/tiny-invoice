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
		$rows = $db->get_all('	SELECT DISTINCT i.invoice_contact_id
								FROM invoice AS i
								WHERE 1
								AND	i.paid = 0
								AND i.send_reminder_mail = 1
								AND DATEDIFF(NOW(), i.expiration_date) > 7');
		foreach ($rows as $row) {
			$this->send_customer_reminder($row['invoice_contact_id']);
		}

		$this->schedule('7 days');
	}

	/**
	 * Send customer reminder
	 *
	 * @access private
	 * @param int $invoice_contact_id
	 */
	private function send_customer_reminder($invoice_contact_id) {
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
										AND i.invoice_contact_id = ?', [ $invoice_contact_id ]);
		$invoice_contact = Invoice_Contact::get_by_id($invoice_contact_id);

		$email = new Email('invoice_reminder');
		$email->add_to($invoice_contact->email, $invoice_contact->firstname . ' ' . $invoice_contact->lastname);
		$email->set_sender($company_info['email'], $company_info['company']);

		$invoices = [];
		foreach ($invoice_rows as $invoice_row) {
			$invoice = Invoice::get_by_id($invoice_row['id']);
			$email->add_file($invoice->file);

			$invoices[] = $invoice;
		}
		$email->assign('invoices', $invoices);

		echo 'sending reminder to user: ' . $invoice_contact->firstname . ' ' . $invoice_contact->lastname . ' (' . $invoice_contact->email . ') ' . "\n";
		$email->send();
	}
}
