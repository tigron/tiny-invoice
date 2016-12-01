<?php
/**
 * Invoice_Method_Clickpost class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Invoice_Method_Clickpost extends Invoice_Method {

	public function send(Invoice $invoice) {
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

		$transport->add_variable('Subject', 'Invoice ' . $invoice->number);
		$transport->add_variable('FromCompany', Setting::get_by_name('company')->value);
		$customer_contact = $invoice->customer_contact;
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
		$transport->add_variable('NeedValidation', 0);

		$transport->add_attachment($invoice->get_pdf());

		$submission = new \Esker\Submission($session);
		$result = $submission->submit_transport($transport);
		Log::create('Invoice sent via Click & Post, tracking: ' . $result->submissionID, $invoice);

		$session->logout();
	}
}
