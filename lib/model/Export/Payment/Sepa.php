<?php
/**
 * Export_Payment_Sepa class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use Sepa\TransferFile;

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


		$sepaFile = new TransferFile();
		$sepaFile->messageIdentification = $this->id;
		$sepaFile->initiatingPartyName = Setting::get_by_name('company')->value;


		foreach ($documents as $document) {
			/*
			* Add a payment to the SEPA file. This method
			* may be called more than once to add multiple
			* payments to the same file.
			*/
			$payment1 = $sepaFile->addPaymentInfo(array(
				'id'                    => $document->id,
				'debtorName'            => Setting::get_by_name('company')->value,
				'debtorAccountIBAN'     => Setting::get_by_name('iban')->value,
				'debtorAgentBIC'        => Setting::get_by_name('bic')->value
			//  'debtorAccountCurrency' => 'GPB', // optional, defaults to 'EUR'
			//  'categoryPurposeCode'   => 'SUPP', // optional, defaults to NULL
			));

			/*
			* Add a credit transfer to the payment. This method
			* may be called more than once to add multiple
			* transfers for the same payment.
			*/
			if ($document->payment_structured_message != '') {
				$message = str_replace('/', '', $document->payment_structured_message);
			} else {
				$message = $document->payment_message;
			}
			$payment1->addCreditTransfer(array(
				'id'                    => $document->id,
				'currency'              => 'EUR',
				'amount'                => $document->price_incl, // or as float: 0.02 or as integer: 2
				'creditorBIC'           => $document->supplier->bic,
				'creditorName'          => $document->supplier->company,
				'creditorAccountIBAN'   => $document->supplier->iban,
				'remittanceInformation' => $message,
			));
		}

		/* Generate the file and return the XML string. */
		$content = $sepaFile->asXML();

		if ($data['mark_paid']) {
			foreach ($documents as $document) {
				$document->paid = true;
				$document->save();
			}
		}

		$file = \Skeleton\File\File::store('payment_sepa' . date('Ymd') . '.xml', $content);
 		$this->file_id = $file->id;
		$this->save();
	}

}
