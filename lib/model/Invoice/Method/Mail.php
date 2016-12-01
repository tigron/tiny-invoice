<?php
/**
 * Invoice_Method_Mail class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Invoice_Method_Mail extends Invoice_Method {

	/**
	 * Send the invoice via email
	 *
	 * @access public
	 * @param Invoice $invoice
	 */
	public function send(Invoice $invoice) {
		$mail = new Email('invoice', $invoice->customer_contact->language);
		if ($invoice->customer_contact->email != '') {
			$mail->add_to($invoice->customer_contact->email, $invoice->customer_contact->firstname . ' ' . $invoice->customer_contact->lastname);
		}
		if ($invoice->customer_contact->email == '' and $invoice->customer->email != '') {
			$mail->add_to($invoice->customer->email, $invoice->customer->firstname . ' ' . $invoice->customer->lastname);
		}
		if ($invoice->customer_contact->email != $invoice->customer->email AND $invoice->customer->email != '') {
			$mail->add_cc($invoice->customer->email, $invoice->customer->firstname . ' ' . $invoice->customer->lastname);
		}

		try {
			$email_from = Setting::get_by_name('email')->value;
			$company = Setting::get_by_name('company')->value;
			$mail->set_sender($email_from, $company);
		} catch (Exception $e) {
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
}
