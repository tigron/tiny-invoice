<?php

use PhpOffice\PhpSpreadsheet\Writer\Ods\Settings;

/**
 * Invoice_Method_Mail class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Invoice_Method_Mail extends Invoice_Method {

	/**
	 * Remind
	 *
	 * @access public
	 * @param Customer_Contact $customer_contact
	 */
	public function remind(Customer_Contact $customer_contact) {
		$mail = new Email('invoice_reminder', $customer_contact->language);

		$to = '';

		if ($customer_contact->email != '') {
			$to = $customer_contact->email;
			$mail->add_to($customer_contact->email, $customer_contact->firstname . ' ' . $customer_contact->lastname);
		}

		if ($customer_contact->email == '' and $customer_contact->customer->email != '') {
			$to = $customer_contact->customer->email;
			$mail->add_to($customer_contact->customer->email, $customer_contact->customer->firstname . ' ' . $customer_contact->customer->lastname);
		}

		if ($customer_contact->email != $customer_contact->customer->email and $customer_contact->customer->email != '') {
			$mail->add_cc($customer_contact->customer->email, $customer_contact->customer->firstname . ' ' . $customer_contact->customer->lastname);
		}

		$mail->set_sender(Setting::get('email'), Setting::get('company'));

		$invoices = $customer_contact->get_expired_invoices();
		$mail->assign('invoices', $invoices);
		$mail->assign('customer_contact', $customer_contact);
		foreach ($invoices as $invoice) {
			$mail->add_attachment($invoice->get_pdf());
		}
		$mail->send();

		foreach ($invoices as $invoice) {
			Log::create('Sending reminder to ' . $to, $invoice);
		}
	}

	/**
	 * Send the invoice via email
	 *
	 * @access public
	 * @param Invoice $invoice
	 */
	public function send_invoice(Invoice $invoice) {
		$mail = new Email('invoice', $invoice->customer_contact->language);
		if ($invoice->customer_contact->email != '') {
			$mail->add_to($invoice->customer_contact->email, $invoice->customer_contact->firstname . ' ' . $invoice->customer_contact->lastname);
		}
		if ($invoice->customer_contact->email == '' and $invoice->customer->email != '') {
			$mail->add_to($invoice->customer->email, $invoice->customer->firstname . ' ' . $invoice->customer->lastname);
		}
		if ($invoice->customer_contact->email != $invoice->customer->email and $invoice->customer->email != '') {
			$mail->add_cc($invoice->customer->email, $invoice->customer->firstname . ' ' . $invoice->customer->lastname);
		}

		try {
			$email_from = Setting::get_by_name('email')->value;
			$company = Setting::get_by_name('company')->value;
			$mail->set_sender($email_from, $company);
		} catch (Exception $e) {
		}

		$conditions_file_setting = Setting::get_by_name('file_id');
		if (isset($conditions_file_setting->value) && $conditions_file_setting->value != 0 && $conditions_file_setting->value != "") {
			$conditions_file = File::get_by_id($conditions_file_setting->value);
			$mail->add_attachment($conditions_file);
		}

		$mail->add_attachment($invoice->get_pdf());
		$mail->assign('invoice', $invoice);

		try {
			$mail->send();
		} catch (Exception $e) {
			throw new \Exception('Mail could not be sent. Error: ' . $e->getMessage());
		}
		Log::create('Invoice sent via email', $invoice);
	}

	/**
	 * Send the creditnote via email
	 *
	 * @access public
	 * @param Invoice $invoice
	 */
	public function send_creditnote(Creditnote $creditnote) {
		$mail = new Email('creditnote', $creditnote->customer_contact->language);
		if ($creditnote->customer_contact->email != '') {
			$mail->add_to($creditnote->customer_contact->email, $creditnote->customer_contact->firstname . ' ' . $creditnote->customer_contact->lastname);
		}
		if ($creditnote->customer_contact->email == '' and $creditnote->customer->email != '') {
			$mail->add_to($creditnote->customer->email, $creditnote->customer->firstname . ' ' . $creditnote->customer->lastname);
		}
		if ($creditnote->customer_contact->email != $creditnote->customer->email and $creditnote->customer->email != '') {
			$mail->add_cc($creditnote->customer->email, $creditnote->customer->firstname . ' ' . $creditnote->customer->lastname);
		}

		try {
			$email_from = Setting::get_by_name('email')->value;
			$company = Setting::get_by_name('company')->value;
			$mail->set_sender($email_from, $company);
		} catch (Exception $e) {
		}

		$conditions_file_setting = Setting::get_by_name('file_id');
		if (isset($conditions_file_setting->value) && $conditions_file_setting->value != 0 && $conditions_file_setting->value != "") {
			$conditions_file = File::get_by_id($conditions_file_setting->value);
			$mail->add_attachment($conditions_file);
		}

		$mail->add_attachment($creditnote->get_pdf());
		$mail->assign('creditnote', $creditnote);

		try {
			$mail->send();
		} catch (Exception $e) {
			throw new \Exception('Mail could not be sent. Error: ' . $e->getMessage());
		}
		Log::create('Creditnote sent via email', $creditnote);
	}
}
