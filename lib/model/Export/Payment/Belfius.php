<?php
/**
 * Export_Payment_Belfius class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Export_Payment_Belfius extends Export {

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

		$output = [];
		$output[] = [
			'A' => 'Individue(e)l(e) transactie(s)',
			'B' => 'Aantal transacties',
			'C' => count($payments),
			'D' => 'Total bedrag',
			'E' => str_replace('.', ',', $total_price),
			'F' => '',
			'G' => '',
			'H' => '',
			'I' => '',
			'J' => '',
			'K' => '',
			'L' => '',
		];
		// Empty line
		$output[] = [
			'A' => 'Rekening opdrachtgever',
			'B' => 'Naam opdrachtgever',
			'C' => 'Uitvoeringsdatum',
			'D' => 'Bedrag',
			'E' => 'Rekening begustigde',
			'F' => 'Naam begunstigde',
			'G' => 'Adres begunstigde - straat en nummer',
			'H' => 'Adres begunstigde - postcode en localiteit',
			'I' => 'Land begunstigde',
			'J' => 'Mededeling',
			'K' => 'Code dringend',
			'L' => 'Referte opdrachtgever',
		];

		$iban = $payment_list->bank_account->number;
		$company = Setting::get_by_name('company')->value;

		foreach ($payments as $payment) {
			$document = $payment->document;
			if ($payment->payment_structured_message != '') {
				$message = str_replace('/', '', $payment->payment_structured_message);
			} else {
				$message = $payment->payment_message;
			}

			if (!$data['pay_on_expiration_date']) {
				$document->expiration_date = date('Y-m-d');
			}


			if (strtotime($document->expiration_date) < time()) {
				$expiration_date = time();
			} else {
				$expiration_date = strtotime($document->expiration_date);
			}
			$output[] = [
				'A' => str_replace(' ', '', $iban),
				'B' => $company,
				'C' => date('d/m/Y', $expiration_date),
				'D' => str_replace('.', ',', $payment->amount),
				'E' => $payment->bank_account_number,
				'F' => $document->supplier->company,
				'G' => $document->supplier->street . ' ' . $document->supplier->housenumber,
				'H' => $document->supplier->zipcode . ' ' . $document->supplier->city,
				'I' => $document->supplier->country->name,
				'J' => $message,
				'K' => 'N',
				'L' => ''
			];
		}

		$content = '';
		foreach ($output as $line) {
			$content .= implode(';', $line) . ';' . "\n";
		}

		if ($data['mark_paid']) {
			foreach ($payments as $payment) {
				$document = $payment->document;
				$document->paid = true;
				$document->save();
			}
		}

		$file = \Skeleton\File\File::store('payment_belfius_' . date('Ymd') . '.csv', $content);
 		$this->file_id = $file->id;
		$this->save();

		foreach ($payments as $payment) {
			$document = $payment->document;
			Log::create('Payment requested via export ' . $this->id, $document);
		}
	}

}
