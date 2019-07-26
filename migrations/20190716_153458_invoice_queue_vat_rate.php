<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20190716_153458_Invoice_queue_vat_rate extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("
			ALTER TABLE `invoice_queue_recurring`
			ADD `vat_rate_id` int(11) NULL AFTER `vat`,
			ADD FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rate` (`id`);
		", []);

		$db->query("
			ALTER TABLE `invoice_queue_recurring`
			CHANGE `vat` `vat_rate_value` decimal(10,2) NOT NULL AFTER `price`;
		", []);


		// We're assuming none of the VAT rates have changed, ever
		$invoice_queue_recurring_ids = $db->get_column('SELECT id FROM invoice_queue_recurring', []);
		$settings = Setting::get_as_array();

		foreach ($invoice_queue_recurring_ids as $invoice_queue_recurring_id) {
			$invoice_queue_recurring = Invoice_Queue_Recurring::get_by_id($invoice_queue_recurring_id);
			if ($invoice_queue_recurring->vat_rate_value > 0) {

				if (strtotime($invoice_queue_recurring->invoice_queue_recurring_group->created) < strtotime('2015-01-01')) {
					$country_id = $settings['country_id'];
				} else {
					$country_id = $invoice_queue_recurring->invoice_queue_recurring_group->customer_contact->country_id;
				}

				$vat_rate_id = $db->get_one('SELECT vat_rate_id FROM vat_rate_country WHERE country_id = ? AND vat = ? ORDER BY vat_rate_id DESC LIMIT 1', [$country_id, $invoice_queue_recurring->vat_rate_value]);
				if ($vat_rate_id !== null) {
					$vat_rate = Vat_Rate::get_by_id($vat_rate_id);
					$invoice_queue_recurring->vat_rate = $vat_rate;
					$invoice_queue_recurring->save();
					continue;
				}

				// Fallback to country of company
				$vat_rate_id = $db->get_one('SELECT vat_rate_id FROM vat_rate_country WHERE country_id = ? AND vat = ? ORDER BY vat_rate_id DESC LIMIT 1', [$settings['country_id'], $invoice_queue_recurring->vat_rate_value]);
				if ($vat_rate_id !== null) {
					$vat_rate = Vat_Rate::get_by_id($vat_rate_id);
					$invoice_queue_recurring->vat_rate = $vat_rate;
					$invoice_queue_recurring->save();
					continue;
				}
			}
		}


		$db->query("
			ALTER TABLE `invoice_queue`
			CHANGE `vat` `vat_rate_value` decimal(10,2) NOT NULL AFTER `price`,
			ADD `vat_rate_id` int(11) NULL AFTER `vat_rate_value`,
			ADD FOREIGN KEY (`vat_rate_id`) REFERENCES `vat_rate` (`id`);
		", []);


		// We're assuming none of the VAT rates have changed, ever
		$invoice_queue_ids = $db->get_column('SELECT id FROM invoice_queue', []);
		$settings = Setting::get_as_array();

		foreach ($invoice_queue_ids as $invoice_queue_id) {
			$invoice_queue = Invoice_Queue::get_by_id($invoice_queue_id);
			if ($invoice_queue->vat_rate_value > 0) {

				if (strtotime($invoice_queue->created) < strtotime('2015-01-01')) {
					$country_id = $settings['country_id'];
				} else {
					$country_id = $invoice_queue->customer_contact->country_id;
				}

				$vat_rate_id = $db->get_one('SELECT vat_rate_id FROM vat_rate_country WHERE country_id = ? AND vat = ? ORDER BY vat_rate_id DESC LIMIT 1', [$country_id, $invoice_queue->vat_rate_value]);
				if ($vat_rate_id !== null) {
					$vat_rate = Vat_Rate::get_by_id($vat_rate_id);
					$invoice_queue->vat_rate = $vat_rate;
					$invoice_queue->save();
					continue;
				}

				// Fallback to country of company
				$vat_rate_id = $db->get_one('SELECT vat_rate_id FROM vat_rate_country WHERE country_id = ? AND vat = ? ORDER BY vat_rate_id DESC LIMIT 1', [$settings['country_id'], $invoice_queue->vat_rate_value]);
				if ($vat_rate_id !== null) {
					$vat_rate = Vat_Rate::get_by_id($vat_rate_id);
					$invoice_queue->vat_rate = $vat_rate;
					$invoice_queue->save();
					continue;
				}
			}
		}

	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
