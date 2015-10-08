<?php
/**
 * Web Module Administrative Invoice
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Invoice extends Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = true;

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = 'administrative/invoice.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();

		$pager = new Pager('invoice');

		$pager->add_sort_permission('number');
		$pager->add_sort_permission('created');
		$pager->add_sort_permission('customer.lastname');
		$pager->add_sort_permission('customer.company');
		$pager->add_sort_permission('price_incl');
		$pager->add_sort_permission('price_excl');
		$pager->add_sort_permission('paid');
		$pager->set_direction('desc');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		$template = Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Create an invoice: select customer
	 *
	 * @access public
	 */
	public function display_create_step1() {
		$template = Template::Get();

		if (!isset($_SESSION['invoice'])) {
			$_SESSION['invoice'] = new Invoice();
			$_SESSION['invoice']->load_array(array('customer_id'=>0, 'invoice_contact_id' => 0, 'type' => 'D'));
		}

		if (isset($_GET['customer_id']) AND isset($_GET['invoice_contact_id'])) {
			$_SESSION['invoice']->customer_id = $_GET['customer_id'];
			$_SESSION['invoice']->invoice_contact_id = $_GET['invoice_contact_id'];
			Session::Redirect('/administrative/invoice?action=create_step3');
		}

		if (isset($_POST['customer_id'])) {
			if ($_POST['customer_id'] == '') {
				$template->assign('errors', 'select_customer');
			} else {
				$_SESSION['invoice']->customer_id = $_POST['customer_id'];
				$_SESSION['invoice']->type = $_POST['type'];
				Session::Redirect('/administrative/invoice?action=create_step2');
			}
		}


		$template->assign('action', 'create_step1');
	}

	/**
	 * Create an invoice: select invoice contact
	 *
	 * @access public
	 */
	public function display_create_step2() {
		$template = Template::Get();
		if (isset($_POST['invoice_contact_id'])) {
			if ($_POST['invoice_contact_id'] == '') {
				$template->assign('errors', 'select_invoice_contact');
			} else {
				$_SESSION['invoice']->invoice_contact_id = $_POST['invoice_contact_id'];
				Session::Redirect('/administrative/invoice?action=create_step3');
			}
		}

		$invoice_contacts = $_SESSION['invoice']->customer->get_active_invoice_contacts();

		$template->assign('invoice_contacts', $invoice_contacts);
		$template->assign('customer', $_SESSION['invoice']->customer);
		$template->assign('languages', Language::get_all());
		$template->assign('countries', Country::get_grouped());
		$template->assign('action', 'create_step2');
	}

	/**
	 * Create an invoice: add invoice items
	 *
	 * @access public
	 */
	public function display_create_step3() {
		$template = Template::Get();

		if (isset($_POST['invoice_item'])) {

			$errors = [];
			$invoice_items = [];
			foreach ($_POST['invoice_item'] as $row => $item) {
				$invoice_item = new Invoice_Item();
				if (trim($item['invoice_queue_id']) == '') {
					unset($item['invoice_queue_id']);
				}
				$invoice_item->load_array($item);
				if ($invoice_item->validate($item_errors) === false) {
					$errors[$row] = $item_errors;
				} else {
					$invoice_items[] = $invoice_item;
				}
			}

			if (count($errors) > 0) {
				$template->assign('errors', $errors);
			} else {
				$invoice = $_SESSION['invoice'];
				$invoice->expiration_date = date('YmdHis', strtotime('+2 weeks'));
				$invoice->generate_number();
				$invoice->save();

				foreach ($invoice_items as $invoice_item) {
					$invoice->add_invoice_item($invoice_item);

					if (!empty($invoice_item->invoice_queue_id)) {
						$invoice_queue = Invoice_Queue::get_by_id($invoice_item->invoice_queue_id);
						$invoice_queue->processed_to_invoice_item_id = $invoice_item->id;
						$invoice_queue->save();
					}
				}

				unset($_SESSION['invoice']);

				Session::Redirect('/administrative/invoice');

			}

		}

		$invoice_queue_items = Invoice_Queue::get_unprocessed_by_invoice_contact($_SESSION['invoice']->invoice_contact);
		$template->assign('invoice_queue_items', $invoice_queue_items);
		$template->assign('vat_rates', Vat_Rate_Country::get_by_country($_SESSION['invoice']->invoice_contact->country));
		$template->assign('action', 'create_step3');
	}

	/**
	 * Edit an invoice
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::Get();
		$invoice = Invoice::get_by_id($_GET['id']);

		if (isset($_POST['invoice'])) {
			$invoice->send_reminder_mail = $_POST['invoice']['send_reminder_mail'];
			$invoice->save();

			Session::set_sticky('message', 'created');
			Session::Redirect('/administrative/invoice?action=edit&id=' . $invoice->id);
		}

		if (isset($_POST['transfer'])) {
			$this->display_add_transfer();
		}

		$template->assign('invoice', $invoice);
	}

	/**
	 * Add transfer
	 *
	 * @access public
	 */
	public function display_add_transfer() {
		if (!isset($_GET['id']) || $_POST['transfer']['amount'] == '') {
			Session::Redirect('/administrative/invoice');
		}

		$invoice = Invoice::get_by_id($_GET['id']);

		$transfer = new Transfer();
		$transfer->type = TRANSFER_TYPE_PAYMENT_MANUAL;
		$transfer->amount = $_POST['transfer']['amount'];
		$transfer->invoice_id = $invoice->id;
		$transfer->save();

		$invoice->add_transfer($transfer);

		Session::Redirect('/administrative/invoice?action=edit&id=' . $invoice->id);
	}

	/**
	 * Download PDF
	 *
	 * @access public
	 */
	public function display_download() {
		$invoice = Invoice::get_by_id($_GET['id']);
		$file = $invoice->get_pdf();
		$file->client_inline();
	}

	/**
	 * Email PDF
	 *
	 * @access public
	 */
	public function display_send() {
		$invoice = Invoice::get_by_id($_GET['id']);
		$invoice->send_invoice_email();

		Session::set_sticky('message', 'invoice_sent');
		Session::Redirect('/administrative/invoice');
	}

}
