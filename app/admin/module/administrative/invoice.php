<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/model/Invoice.php';
require_once LIB_PATH . '/model/Customer.php';
require_once LIB_PATH . '/model/Invoice/Contact.php';
require_once LIB_PATH . '/base/Web/Pager.php';

class Module_Administrative_Invoice extends Web_Module {
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
		$extra_conditions = array();
		if (isset($_POST['search'])) {
			$extra_conditions['%search%'] = $_POST['search'];
			$pager = new Web_Pager('invoice', 'id', 'DESC', 1, $extra_conditions);
		} else {
			$pager = new Web_Pager('invoice', 'id', 'DESC');
		}

		$template = Web_Template::Get();
		$session = Web_Session_Sticky::Get();
		if (isset($session->message)) {
			$template->assign('message', $session->message);
			unset($session->message);
		}

		$template->assign('pager', $pager);
	}

	/**
	 * AJAX search customer
	 *
	 * @access public
	 */
	public function display_ajax_search_customer() {
		$extra_conditions = array( '%search%' => $_POST['search']);
		$customers = Customer::get_paged(1, 'ASC', 1, $extra_conditions);

		$data = array();
		foreach ($customers as $customer) {
			$data[] = $customer->get_info();
		}
		echo json_encode($data);
		exit;
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Web_Template::Get();
		$invoice = Invoice::get_by_id($_GET['id']);

		if (isset($_POST['invoice'])) {
			$invoice->send_reminder_mail = $_POST['invoice']['send_reminder_mail'];
			$invoice->save();
			$template->assign('saved', true);
		}

		if (isset($_POST['transfer'])) {
			$this->display_add_transfer();
		}

		$template->assign('invoice', $invoice);
	}

	/**
	 * Create an invoice
	 *
	 * @access public
	 */
	public function display_create() {
		if (!isset($_POST['customer_id'])) {
			$this->display_create_step1();
		} elseif (!isset($_POST['invoice_contact_id'])) {
			$this->display_create_step2();
		} elseif (!isset($_POST['invoice_item'])) {
			$this->display_create_step3();
		} else {
			$this->display_create_step4();
		}
	}

	/**
	 * Create invoice: step1
	 * Choose a customer
	 *
	 * @access public
	 */
	public function display_create_step1() {
		$template = Web_Template::Get();
		$template->assign('action', 'create_step1');
	}

	/**
	 * Create invoice: step2
	 * Choose an invoice contact
	 *
	 * @access public
	 */
	public function display_create_step2() {
		$template = Web_Template::Get();
		$template->assign('action', 'create_step2');
		try {
			$customer = Customer::get_by_id($_POST['customer_id']);
		} catch (Exception $e) {
			$template->assign('error', true);
			$this->display_create_step1();
			return;
		}

		$template->assign('customer', $customer);
		$invoice_contacts = Invoice_Contact::get_by_customer($customer);
		$template->assign('invoice_contacts', $invoice_contacts);
		$template->assign('countries', Country::get_grouped());
	}

	/**
	 * Create invoice: step3
	 * Choose the invoice items
	 *
	 * @access public
	 */
	public function display_create_step3() {
		if (isset($_POST['invoice_contact'])) {
			if ($_POST['invoice_contact_id'] == 0 OR $_POST['invoice_contact_id'] == -1) {
				$invoice_contact = new Invoice_Contact();
			} else {
				$invoice_contact = Invoice_Contact::get_by_id($_POST['invoice_contact_id']);
			}
			$invoice_contact->load_array($_POST['invoice_contact']);
			$invoice_contact->customer_id = $_POST['customer_id'];
			$invoice_contact->save();
		}

		$template = Web_Template::Get();
		$template->assign('action', 'create_step3');
		$template->assign('invoice_contact', $invoice_contact);
		$template->assign('customer', Customer::get_by_id($_POST['customer_id']));
	}

	/**
	 * Create invoice: step4
	 * Create the invoice
	 *
	 * @access public
	 */
	public function display_create_step4() {
		$template = Web_Template::get();
		$invoice = new Invoice();
		$invoice->customer_id = $_POST['customer_id'];
		$invoice->invoice_contact_id = $_POST['invoice_contact_id'];
		$invoice->expiration_date = date('Y-m-d H:i:s', strtotime('+1 month'));
		$invoice->save();

		foreach($_POST['invoice_item']['description'] as $key => $value) {
			$invoice_item = new Invoice_Item();
			$invoice_item->description = $value;
			$invoice_item->price = $_POST['invoice_item']['price'][$key];
			$invoice_item->vat = $_POST['invoice_item']['vat'][$key];
			$invoice_item->save();
			$invoice->add_invoice_item($invoice_item);
		}

		// If you want to send the invoice automatically after creation, uncomment this line
		//$invoice->send_invoice_email();

		Web_Session::Redirect('/administrative/invoice');
	}

	/**
	 * Download PDF
	 *
	 * @access public
	 */
	public function display_download() {
		$invoice = Invoice::get_by_id($_GET['id']);
		$invoice->render()->client_download();
	}

	/**
	 * Email PDF
	 *
	 * @access public
	 */
	public function display_send_invoice() {
		$invoice = Invoice::get_by_id($_GET['id']);
		$invoice->send_invoice_email();

		$session = Web_Session_Sticky::Get();
		$session->message = 'invoice_sent';

		Web_Session::Redirect('/administrative/invoice');
	}

	/**
	 * Add transfer
	 *
	 * @access public
	 */
	public function display_add_transfer() {
		if (!isset($_GET['id']) || $_POST['transfer']['amount'] == '') {
			Web_Session::Redirect('/administrative/invoice');
		}

		$invoice = Invoice::get_by_id($_GET['id']);

		$transfer = new Transfer();
		$transfer->type = TRANSFER_TYPE_PAYMENT_MANUAL;
		$transfer->amount = $_POST['transfer']['amount'];
		$transfer->invoice_id = $invoice->id;
		$transfer->save();

		$invoice->add_transfer($transfer);

		Web_Session::Redirect('/administrative/invoice?action=edit&id=' . $invoice->id);
	}
}
