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
		try {
			$enable_invoice_reminder = Setting::get_by_name('enable_invoice_reminder');
		} catch (Exception $e) {
			return;
		}

		if (!$enable_invoice_reminder->value) {
			$this->schedule('1 day');
			return;
		}

		$grouped = [];
		$remindable_invoices = Invoice::get_remindable();

		foreach ($remindable_invoices as $remindable_invoice) {
			if (!isset($grouped[$remindable_invoice->customer_contact_id])) {
				$grouped[$remindable_invoice->customer_contact_id] = [];
			}
			$grouped[$remindable_invoice->customer_contact_id][] = $remindable_invoice;
		}

		foreach ($grouped as $customer_contact_id => $invoices) {
			$customer_contact = Customer_Contact::get_by_id($customer_contact_id);

			$ids = [];
			foreach ($invoices as $invoice) {
				$ids[] = $invoice->id;
			}

			if (strtotime($customer_contact->last_invoice_reminder) > strtotime('7 days ago')) {
				continue;
			}

			try {
				$customer_contact->invoice_method->remind($customer_contact);
				$customer_contact->last_invoice_reminder = date('Y-m-d H:i:s');
				$customer_contact->save(false);
			} catch (\Exception $e) {
				echo $customer_contact->get_identifier() . ';NOK;' . $customer_contact->customer_id . ';' . $customer_contact->invoice_method->name . ';' . implode(',', $ids) . "\n";
				continue;
			}

			echo $customer_contact->get_identifier() . ';OK;' . $customer_contact->customer_id . ';' . $customer_contact->invoice_method->name . ';' . implode(',', $ids) . "\n";
		}

		$this->schedule('1 day');
	}

}
