<?php
/**
 * Export_Payment_Sepa class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Export_Payment_Sepa extends Export {

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		$data = $this->get_data();
		$payment_list = Payment_List::get_by_id($data['payment_list_id']);
		$payments = $payment_list->get_payments();
		$total_price = 0;

		foreach ($payments as $payment) {
			$document = $payment->document;
			if ($document->classname != 'Document_Incoming_Invoice') {
				throw new Exception('Incorrect document type');
			}
			$total_price += $document->price_incl;
		}

		/**
		 * Sort the documents
		 */
		$sorted = [];
		foreach ($payments as $payment) {
			$document = $payment->document;
			$document_date = new DateTime($document->expiration_date);
			if ($document_date < new Datetime()) {
				$document_date = new DateTime();
			}

			if ($data['pay_on_expiration_date']) {
				$expiration_date = $document_date->format('Y-m-d');
			} else {
				$expiration_date = (new DateTime())->format('Y-m-d');
			}

			if (!isset($sorted[$expiration_date])) {
				$sorted[$expiration_date] = [];
			}
			$sorted[$expiration_date][] = $payment;
		}
		ksort($sorted);

		$organization = new \Tigron\Sepa\Organization();
		$organization->name = Setting::get_by_name('company')->value;
		/**
		 * Set the identification
		 *
		 * Possible types: bic, bei or kbo-bce
		 */
		$organization->set_identification('kbo-bce', Setting::get_by_name('vat')->value);

		$credit = new \Tigron\Sepa\File\Credit();
		$credit->messageIdentification = $this->id;
		$credit->initiatingParty = $organization;

		$debtor = new \Tigron\Sepa\Debtor();
		$debtor->name = Setting::get_by_name('company')->value;
		$debtor->country = Country::get_by_id(Setting::get_by_name('country_id')->value)->iso2;
		$debtor->zipcode = Setting::get_by_name('zipcode')->value;
		$debtor->city = Setting::get_by_name('city')->value;
		$debtor->street = Setting::get_by_name('street')->value;
		$debtor->housenumber = Setting::get_by_name('housenumber')->value;

		foreach ($sorted as $key => $payments) {
			// Create a payment
			$sepa_payment = new \Tigron\Sepa\Payment();
			$sepa_payment->paymentInformationIdentification = 'export_' . $this->id;

			$document_date = DateTime::createFromFormat('Y-m-d', $key);

			$sepa_payment->requestedExecutionDate = $document_date;
			$sepa_payment->debtorAccount = $payment_list->bank_account->number;
			$sepa_payment->debtorAgent = $payment_list->bank_account->bic;
			$sepa_payment->debtor = $debtor;

			foreach ($payments as $payment) {
				$document = $payment->document;
				// Create a transaction
				$supplier = $document->supplier;
				$creditor = new \Tigron\Sepa\Creditor();
				$creditor->name = $supplier->company;
				$creditor->country = $supplier->country->iso2;
				$creditor->zipcode = $supplier->zipcode;
				$creditor->city = $supplier->city;
				$creditor->street = $supplier->street;
				$creditor->housenumber = $supplier->housenumber;

				$transaction = new \Tigron\Sepa\Transaction();
				$transaction->paymentIdentification = 'document_' . $payment->document_id;
				$transaction->amount = $payment->amount;
				$transaction->creditorAgent = $payment->bank_account_bic;
				$transaction->creditorAccount = $payment->bank_account_number;
				$transaction->creditor = $creditor;
				if ($payment->payment_message != '') {
					$transaction->unstructured_message = $payment->payment_message;
				} else {
					$transaction->structured_message = $payment->payment_structured_message;
				}

				$sepa_payment->transactions[] = $transaction;

				if ($data['mark_paid']) {
					$document = $payment->document;
					$document->paid = true;
					$document->save();
				}


			}
			$credit->payments[] = $sepa_payment;
		}

		$xml = $credit->render();
		$file = \Skeleton\File\File::store('payment_sepa' . date('Ymd') . '.xml', $xml);
 		$this->file_id = $file->id;
		$this->save();

		foreach ($payments as $payment) {
			$document = $payment->document;
			Log::create('Payment requested via export ' . $this->id, $document);
		}
	}

}
