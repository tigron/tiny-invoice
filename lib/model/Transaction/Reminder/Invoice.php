<?php
/**
 * Transaction_Order
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once LIB_PATH . '/model/Transaction.php';
require_once LIB_PATH . '/model/Invoice.php';
require_once LIB_PATH . '/base/Email.php';

class Transaction_Reminder_Invoice extends Transaction {

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int $id
	 */
	public function __construct($id = null) {
		parent::__construct($id);
		$this->type = 'Reminder_Invoice';
	}

	/**
	 * Run
	 *
	 * @access private
	 */
	public function run() {
		$db = Database::Get();
		$rows = $db->getAll('	SELECT DISTINCT i.invoice_contact_id
								FROM invoice AS i
								WHERE 1
								AND	i.paid = 0
								AND i.send_reminder_mail = 1
								AND DATEDIFF(NOW(), i.expiration_date) > 7');
		foreach ($rows as $row) {
			$this->send_customer_reminder($row['invoice_contact_id']);
		}
		$this->sleep('7 days');
	}

	/**
	 * Send customer reminder
	 *
	 * @access private
	 * @param int $user_id
	 */
	private function send_customer_reminder($invoice_contact_id) {
		$db = Database::Get();
		$config = Config::Get();

		$invoice_rows = $db->getAll('	SELECT i.*
										FROM invoice AS i
										WHERE 1
										AND i.paid = 0
										AND i.send_reminder_mail = 1
										AND i.expiration_date < NOW()
										AND i.invoice_contact_id = ?', array($invoice_contact_id));
		$invoice_contact = Invoice_Contact::get_by_id($invoice_contact_id);

		$mail = new Email('invoice_reminder');
		$mail->add_recipient($invoice_contact);
		$mail->set_sender($config->company_info['email'], $config->company_info['company']);

		$invoices = array();
		foreach ($invoice_rows as $invoice_row) {
			$invoice = Invoice::get_by_id($invoice_row['id']);
			$invoices[] = $invoice;

			$mail->add_file($invoice->file);
		}
		$mail->assign('invoices', $invoices);
		$mail->assign('company_info', $config->company_info);

		echo 'sending reminder to user: ' . $invoice_contact->firstname . ' ' . $invoice_contact->lastname . ' (' . $invoice_contact->email . ') ' . "\n";
		$mail->send();
	}
}
