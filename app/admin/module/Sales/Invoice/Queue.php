<?php
/**
 * Web Module Administrative Invoice Queue
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Sales\Invoice;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Application\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Queue extends Module {
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
	protected $template = 'sales/invoice/queue.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();

		$pager = new Pager('invoice_queue');

		if (isset($_POST['status'])) {
			if ($_POST['status'] == 'processed') {
				$pager->add_condition('processed_to_invoice_item_id', 'IS NOT', NULL);
			} elseif ($_POST['status'] == 'unprocessed') {
				$pager->add_condition('processed_to_invoice_item_id', 'IS', NULL);
			}
		} else {
			$pager->add_condition('processed_to_invoice_item_id', 'IS', NULL);
		}
		$pager->add_condition('deleted', 'IS', NULL);

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('price');
		$pager->add_sort_permission('qty');
		$pager->add_sort_permission('description');
		$pager->add_sort_permission('customer.company');
		$pager->add_sort_permission('processed_to_invoice_item_id');

		if (isset($_POST['search'])) {
			$search_fields = [
				'invoice_queue.description',
				'customer.company',
				'customer.lastname'
			];
			$pager->set_search($_POST['search'], $search_fields);
		}

		$pager->set_direction('desc');
		$pager->page();

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/sales/invoice/queue');
		}

		$template = Template::get();
		$template->assign('pager', $pager);
	}

	/**
	 * Create an invoice queue item: select customer
	 *
	 * @access public
	 */
	public function display_create_step1() {
		$template = Template::get();

		if (!isset($_SESSION['invoice_queue'])) {
			$_SESSION['invoice_queue'] = ['items' => [], 'customer_id' => 0, 'customer_contact_id' => 0];
		}

		if (isset($_POST['customer_id'])) {
			if ($_POST['customer_id'] == '') {
				$template->assign('errors', 'select_customer');
			} else {
				$_SESSION['invoice_queue']['customer_id'] = $_POST['customer_id'];
				Session::redirect('/sales/invoice/queue?action=create_step2');
			}
		}

		$customer = null;
		if ($_SESSION['invoice_queue']['customer_id'] > 0) {
			$customer = \Customer::get_by_id($_SESSION['invoice_queue']['customer_id']);
		}
		$template->assign('customer', $customer);
		$template->assign('action', 'create_step1');
	}

	/**
	 * Create an invoice queue item: select invoice contact
	 *
	 * @access public
	 */
	public function display_create_step2() {
		$template = Template::get();
		if (isset($_POST['customer_contact_id'])) {
			if ($_POST['customer_contact_id'] == '') {
				$template->assign('errors', 'select_customer_contact');
			} else {
				$_SESSION['invoice_queue']['customer_contact_id'] = $_POST['customer_contact_id'];
				Session::redirect('/sales/invoice/queue?action=create_step3');
			}
		}

		$customer = \Customer::get_by_id($_SESSION['invoice_queue']['customer_id']);
		$customer_contacts = $customer->get_active_customer_contacts();

		$template->assign('customer_contacts', $customer_contacts);
		$template->assign('customer', $customer);
		$template->assign('languages', \Language::get_all());
		$template->assign('countries', \Country::get_grouped());
		$template->assign('action', 'create_step2');
	}

	/**
	 * Create an invoice queue item: add invoice items
	 *
	 * @access public
	 */
	public function display_create_step3() {
		$template = Template::get();

		if (isset($_POST['invoice_queue_item'])) {

			$errors = [];
			$invoice_queue_items = [];
			foreach ($_POST['invoice_queue_item'] as $row => $item) {
				$invoice_queue = new \Invoice_Queue();
				$invoice_queue->load_array($item);
				if ($invoice_queue->validate($item_errors) === false) {
					$errors[$row] = $item_errors;
				} else {
					$invoice_queue_items[] = $invoice_queue;
				}
			}

			if (count($errors) > 0) {
				$template->assign('errors', $errors);
			} else {
				foreach ($invoice_queue_items as $invoice_queue_item) {
					$invoice_queue_item->customer_id = $_SESSION['invoice_queue']['customer_id'];
					$invoice_queue_item->customer_contact_id = $_SESSION['invoice_queue']['customer_contact_id'];
					$invoice_queue_item->save();
				}

				unset($_SESSION['invoice_queue']);

				Session::redirect('/sales/invoice/queue');

			}

		}

		$customer_contact = \Customer_Contact::get_by_id($_SESSION['invoice_queue']['customer_contact_id']);
		$template->assign('vat_rates', \Vat_Rate_Country::get_by_country($customer_contact->country));
		$template->assign('action', 'create_step3');
		$template->assign('product_types', \Product_Type::get_all());
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::get();
		$invoice_queue = \Invoice_Queue::get_by_id($_GET['id']);

		if (isset($_POST['invoice_queue'])) {
			$invoice_queue->load_array($_POST['invoice_queue']);
			$invoice_queue->save();

			Session::set_sticky('message', 'updated');
			Session::redirect('/sales/invoice/queue?action=edit&id=' . $invoice_queue->id);
		}

		$template->assign('invoice_queue', $invoice_queue);
		$template->assign('vat_rates', \Vat_Rate_Country::get_by_country($invoice_queue->customer_contact->country));
		$template->assign('product_types', \Product_Type::get_all());
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function display_delete() {
		$template = Template::get();
		$invoice_queue = \Invoice_Queue::get_by_id($_GET['id']);
		$invoice_queue->delete();

		Session::set_sticky('message', 'deleted');
		Session::redirect('/sales/invoice/queue');
	}

	/**
	 * Search customer (ajax)
	 *
	 * @access public
	 */
	public function display_ajax_search() {
		$this->template = null;

		$pager = new Pager('customer');
		$permissions = [
			'lastname' => 'lastname'
		];
		$pager->set_sort_permissions($permissions);
		$pager->set_search($_GET['search']);
		$pager->page();

		$data = [];
		foreach ($pager->items as $customer) {
			$name = $customer->firstname . ' ' . $customer->lastname;
			if ($customer->company != '') {
				$name .= ' (' . $customer->company . ')';
			}
			$data[] = [
				'id' => $customer->id,
				'value' => $name
			];
		}
		echo json_encode($data);
	}

	/**
	 * Load customer (ajax)
	 *
	 * @access public
	 */
	public function display_load_customer() {
		$this->template = null;

		$customer = \Customer::get_by_id($_GET['id']);
		echo json_encode($customer->get_info());
	}

	/**
	 * Batch process
	 *
	 * @access public
	 */
	public function display_batch_process() {
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
	public function display_process_batch() {
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
