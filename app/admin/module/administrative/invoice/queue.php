<?php
/**
 * Web Module Administrative Invoice Queue
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Invoice_Queue extends Module {
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
	protected $template = 'administrative/invoice/queue.twig';

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

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('price');
		$pager->add_sort_permission('customer.lastname');
		$pager->add_sort_permission('processed_to_invoice_item_id');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}

		$pager->set_direction('desc');
		$pager->page();

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
			$_SESSION['invoice_queue'] = ['items' => [], 'customer_id' => 0, 'invoice_contact_id' => 0];
		}

		if (isset($_POST['customer_id'])) {
			if ($_POST['customer_id'] == '') {
				$template->assign('errors', 'select_customer');
			} else {
				$_SESSION['invoice_queue']['customer_id'] = $_POST['customer_id'];
				Session::redirect('/administrative/invoice/queue?action=create_step2');
			}
		}

		$customer = null;
		if ($_SESSION['invoice_queue']['customer_id'] > 0) {
			$customer = Customer::get_by_id($_SESSION['invoice_queue']['customer_id']);
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
		if (isset($_POST['invoice_contact_id'])) {
			if ($_POST['invoice_contact_id'] == '') {
				$template->assign('errors', 'select_invoice_contact');
			} else {
				$_SESSION['invoice_queue']['invoice_contact_id'] = $_POST['invoice_contact_id'];
				Session::redirect('/administrative/invoice/queue?action=create_step3');
			}
		}

		$customer = Customer::get_by_id($_SESSION['invoice_queue']['customer_id']);
		$invoice_contacts = $customer->get_active_invoice_contacts();

		$template->assign('invoice_contacts', $invoice_contacts);
		$template->assign('customer', $customer);
		$template->assign('languages', Language::get_all());
		$template->assign('countries', Country::get_grouped());
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
				$invoice_queue = new Invoice_Queue();
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
					$invoice_queue_item->invoice_contact_id = $_SESSION['invoice_queue']['invoice_contact_id'];
					$invoice_queue_item->save();
				}

				unset($_SESSION['invoice_queue']);

				Session::redirect('/administrative/invoice/queue');

			}

		}

		$invoice_contact = Invoice_Contact::get_by_id($_SESSION['invoice_queue']['invoice_contact_id']);
		$template->assign('vat_rates', Vat_Rate_Country::get_by_country($invoice_contact->country));
		$template->assign('action', 'create_step3');
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::get();
		$invoice_queue = Invoice_Queue::get_by_id($_GET['id']);

		if (isset($_POST['invoice_queue'])) {
			$invoice_queue->load_array($_POST['invoice_queue']);
			$invoice_queue->save();

			$session = Session_Sticky::get();
			$session->message = 'updated';
			Session::Redirect('/administrative/invoice/queue?action=edit&id=' . $invoice_queue->id);
		}
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

		$customer = Customer::get_by_id($_GET['id']);
		echo json_encode($customer->get_info());
	}

}
