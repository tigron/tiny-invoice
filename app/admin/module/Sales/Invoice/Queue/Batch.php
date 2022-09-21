<?php
/**
 * Web Module Administrative Invoice Queue
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Sales\Invoice\Queue;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Application\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Batch extends Module {
	/**
	 * Login required
	 *
	 * @access protected
	 * @var bool $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'sales/invoice/queue/batch.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();

		$pager = new Pager('invoice_queue');
		$pager->add_condition('processed_to_invoice_item_id', 'IS', NULL);
		$pager->add_condition('deleted', 'IS', NULL);
		$pager->set_direction('asc');
		$pager->page(true);
		$invoice_queues = $pager->items;
		$customer_contacts = [];

		foreach ($invoice_queues as $invoice_queue) {
			$customer_contacts[$invoice_queue->customer_contact_id] = $invoice_queue->customer_contact;
		}
		$template->assign('customer_contacts', $customer_contacts);
	}

	/**
	 * Process batch
	 *
	 * @access public
	 */
	public function display_process() {
		if (!isset($_POST['customer_contact'])) {
			Session::redirect('/sales/invoice/queue?action=batch_process');
		}

		if (count($_POST['customer_contact']) == 0) {
			Session::redirect('/sales/invoice/queue?action=batch_process');
		}


		foreach ($_POST['customer_contact'] as $customer_contact_id => $ignore) {
			$customer_contact = \Customer_Contact::get_by_id($customer_contact_id);
			$invoice_queue = $customer_contact->get_outstanding_invoice_queue();
			$total_price = 0;
			foreach ($invoice_queue as $invoice_queue_item) {
				$total_price += $invoice_queue_item->price;
			}

			if ($total_price == 0) {
				continue;
			}
			if (count($invoice_queue) == 0) {
				continue;
			}

			$invoice = new \Invoice();
			$invoice->customer_id = $customer_contact->customer_id;
			$invoice->customer_contact_id = $customer_contact->id;
			$invoice->expiration_date = date('YmdHis', strtotime($_POST['invoice']['expiration_date']));
			$invoice->reference = $customer_contact->reference;
			$invoice->generate_number();
			$invoice->save();

			foreach ($invoice_queue as $invoice_queue_item) {
				$invoice_item = new \Invoice_Item();
				$invoice_item->description = $invoice_queue_item->description;
				$invoice_item->product_type_id = $invoice_queue_item->product_type_id;
				$invoice_item->invoice_queue_id = $invoice_queue_item->id;
				$invoice_item->qty = $invoice_queue_item->qty;
				$invoice_item->price = $invoice_queue_item->price;
				$invoice_item->vat_rate_id = $invoice_queue_item->vat_rate_id;
				$invoice_item->vat_rate_value = $invoice_queue_item->vat_rate_value;
				$invoice_item->save();
				$invoice->add_invoice_item($invoice_item);

				$invoice_queue_item->processed_to_invoice_item_id = $invoice_item->id;
				$invoice_queue_item->save();
			}
			if (isset($_POST['send_invoice'])) {
				$invoice->schedule_send();
			}
			\Log::create('add', $invoice);
		}
		Session::redirect('/sales/invoice/queue');
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.invoice_queue';
	}

}
