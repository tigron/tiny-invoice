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
		$document_ids = $data['document_ids'];
		$total_price = 0;

		$documents = [];
		foreach ($document_ids as $document_id) {
			$document = Document::get_by_id($document_id);
			$documents[] = $document;
			if ($document->classname != 'Document_Incoming_Invoice') {
				throw new Exception('Incorrect document type');
			}
			$total_price += $document->price_incl;
		}

		/**
		 * Sort the documents
		 */
		$sorted = [];
		foreach ($documents as $document) {
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
			$sorted[$expiration_date][] = $document;
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

		foreach ($sorted as $key => $documents) {
			// Create a payment
			$payment = new \Tigron\Sepa\Payment();
			$payment->paymentInformationIdentification = 'export_' . $this->id;

			$document_date = DateTime::createFromFormat('Y-m-d', $key);

			$payment->requestedExecutionDate = $document_date;
			$payment->debtorAccount = str_replace(' ', '', Setting::get_by_name('iban')->value);
			$payment->debtorAgent = Setting::get_by_name('bic')->value;
			$payment->debtor = $debtor;

			foreach ($documents as $document) {

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
				$transaction->paymentIdentification = 'document_' . $document->id;
				$transaction->amount = $document->price_incl;
				$transaction->creditorAgent = $supplier->bic;
				$transaction->creditorAccount = $supplier->iban;
				$transaction->creditor = $creditor;
				if ($document->payment_message != '') {
					$transaction->unstructured_message = $document->payment_message;
				} else {
					$transaction->structured_message = $document->payment_structured_message;
				}

				$payment->transactions[] = $transaction;

				if ($data['mark_paid']) {
					$document->paid = true;
					$document->save();
				}


			}
			$credit->payments[] = $payment;
		}

		$xml = $credit->render();
		$file = \Skeleton\File\File::store('payment_sepa' . date('Ymd') . '.xml', $xml);
 		$this->file_id = $file->id;
		$this->save();
	}

}
