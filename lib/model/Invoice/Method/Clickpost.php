<?php
/**
 * Invoice_Method_Clickpost class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Invoice_Method_Clickpost extends Invoice_Method {

	/**
	 * Remind
	 *
	 * @access public
	 * @param Customer_Contact $customer_contact
	 */
	public function remind(Customer_Contact $customer_contact) {
		$tracking = $this->send_pdf($customer_contact, $customer_contact->get_invoice_reminder_pdf(), 'Reminder ' . $customer_contact->get_identifier());
		foreach ($customer_contact->get_expired_invoices() as $invoice) {
			Log::create('Reminder sent via Click & Post, tracking: ' . $tracking, $invoice);
		}
	}

	/**
	 * Send an invoice
	 *
	 * @access public
	 * @param Invoice $invoice
	 */
	public function send(Invoice $invoice) {
		$tracking = $this->send_pdf($invoice->customer_contact, $invoice->get_pdf(), 'Invoice ' . $invoice->number);
		Log::create('Invoice sent via Click & Post, tracking: ' . $tracking, $invoice);
	}

	/**
	 * Send a document per post
	 *
	 * @access private
	 * @param Customer_Contact $customer_contact
	 * @param File $file
	 * @param string $subject
	 */
	private function send_pdf(Customer_Contact $customer_contact, Skeleton\File\Pdf\Pdf $pdf, $subject) {
		try {
			$username_setting = Setting::get_by_name('click_post_username');
		} catch (Exception $e) {
			$username_setting = new Setting();
			$username_setting->name = 'click_post_username';
			$username_setting->value = '';
		}

		try {
			$password_setting = Setting::get_by_name('click_post_password');
		} catch (Exception $e) {
			$password_setting = new Setting();
			$password_setting->name = 'click_post_password';
			$password_setting->value = '';
		}


		$session = \Esker\Session::get($username_setting->value, $password_setting->value);

		// Now allocate a transport with transportName = 'MODEsker'
		$transport = new \Esker\Transport();
		$transport->recipientType = "";
		$transport->transportIndex = 0;
		$transport->transportName = 'MODEsker';

		$transport->add_variable('Subject', $subject);
		$transport->add_variable('FromCompany', Setting::get_by_name('company')->value);

		$to = '';
		if ($customer_contact->company == '') {
			$to = $customer_contact->firstname . ' ' . $customer_contact->lastname;
		} else {
			$to = $customer_contact->company;
		}

		$transport->add_variable('ToBlockAddress', $to . "\n" . $customer_contact->street . ' ' . $customer_contact->housenumber . "\n" . $customer_contact->zipcode . ' ' . $customer_contact->city . "\n" . $customer_contact->country->name);
		$transport->add_variable('Color', 'Y');
		$transport->add_variable('Cover', 'N');
		$transport->add_variable('BothSided', 'N');
		$transport->add_variable('MaxRetry', 3);
		$config = Config::get();
		if ($config->debug) {
			// Don't send if we are in debug mode
			return;
		} else {
			$transport->add_variable('NeedValidation', 0);
		}

		$transport->add_attachment($pdf);

		$submission = new \Esker\Submission($session);
		$result = $submission->submit_transport($transport);
		$session->logout();
		return $result->submissionID;
	}
}
