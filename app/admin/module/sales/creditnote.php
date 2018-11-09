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

class Web_Module_Sales_Creditnote extends Module {
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
	protected $template = 'sales/creditnote.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$pager = new Pager('creditnote');

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

		if (isset($_POST['date_start']) AND $_POST['date_start'] != '' AND $_POST['date_end'] == '') {
			$pager->add_condition('created', '>=', date('Y-m-d H:i:s', strtotime($_POST['date_start'] . ' 00:00:00')));
		} elseif (isset($_POST['date_end']) AND $_POST['date_end'] != '' AND $_POST['date_start'] == '') {
			$pager->add_condition('created', '<=', date('Y-m-d H:i:s', strtotime($_POST['date_end'] . ' 23:59:59')));
		} elseif (isset($_POST['date_start']) AND $_POST['date_start'] != '' AND isset($_POST['date_end']) AND $_POST['date_end'] != '') {
			$pager->add_condition('created', 'BETWEEN', date('Y-m-d H:i:s', strtotime($_POST['date_start'] . ' 00:00:00')), date('Y-m-d H:i:s', strtotime($_POST['date_end'] . ' 23:59:59')));
		}

		if (isset($_POST['customer_id']) AND $_POST['customer_id'] != '') {
			$pager->add_condition('customer_id', $_POST['customer_id']);
		}

		if (isset($_POST['paid']) AND $_POST['paid'] != '') {
			$pager->add_condition('paid', $_POST['paid']);
		}

		$pager->page();

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/sales/creditnote');
		}

		$template = Template::get();
		$template->assign('pager', $pager);
		$template->assign('customers', Customer::get_all('lastname'));
	}

	/**
	 * Create step1
	 *
	 * @access public
	 */
	public function display_create_step1() {
		if (isset($_POST['invoice_id'])) {
			$_SESSION['creditnote']['invoice_id'] = $_POST['invoice_id'];
			Session::redirect('/sales/creditnote?action=create_step2');
		}
	}

	/**
	 * Create step2
	 *
	 * @access public
	 */
	public function display_create_step2() {
		$invoice = Invoice::get_by_id($_SESSION['creditnote']['invoice_id']);
		$template = Template::get();
		$template->assign('invoice', $invoice);
		$template->assign('product_types', Product_Type::get_all('name'));
		$template->assign('vat_rates', Vat_Rate_Country::get_by_country($invoice->customer_contact->country));

		if (isset($_POST['creditnote_item']) and isset($_SESSION['creditnote']['invoice_id'])) {
			$invoice = Invoice::get_by_id($_SESSION['creditnote']['invoice_id']);

			$errors = [];
			$total_price = 0;
			$creditnote_items = [];
			foreach ($_POST['creditnote_item'] as $row => $item) {
				$creditnote_item = new Creditnote_Item();
				if (trim($item['invoice_item_id']) == '') {
					unset($item['invoice_item_id']);
				}

				$creditnote_item->load_array($item);

				if ($item['vat_rate_id'] > 0) {
					$vat_rate = Vat_Rate::get_by_id($item['vat_rate_id']);
					$vat_rate_country = Vat_Rate_Country::get_by_vat_rate_country($vat_rate, $invoice->customer_contact->country);
					$creditnote_item->vat_rate_value = $vat_rate_country->vat;
				} else {
					$creditnote_item->vat_rate_id = null;
					$creditnote_item->vat_rate_value = 0;
				}

				if ($invoice->vat_mode == 'group') {
					$creditnote_item->price_excl = $creditnote_item->price;
				} else {
					$creditnote_item->price_incl = $creditnote_item->price;
				}

				$creditnote_item->calculate_prices();

				if ($creditnote_item->validate($item_errors) === false) {
					$errors[$row] = $item_errors;
				} else {
					$creditnote_items[] = $creditnote_item;
				}

				$total_price += $creditnote_item->price_excl;
			}
			if ($total_price == 0) {
				$errors[-1] = 'free';
			}

			if (count($errors) > 0) {
				$template->assign('errors', $errors);
			} else {
				$creditnote = new Creditnote();
				$creditnote->customer_id = $invoice->customer_id;
				$creditnote->customer_contact_id = $invoice->customer_contact_id;
				$creditnote->vat_mode = $invoice->vat_mode;
				$creditnote->generate_number();
				$creditnote->save();

				foreach ($creditnote_items as $creditnote_item) {
					$creditnote->add_creditnote_item($creditnote_item);
				}

				$transfer = new Transfer();
				$transfer->type = TRANSFER_TYPE_PAYMENT_CREDITNOTE;
				$transfer->amount = $creditnote->price_incl;
				$transfer->save();

				Log::create('Creditnote ' . $creditnote->number . ' created for invoice', $invoice);
				$invoice->add_transfer($transfer);

				unset($_SESSION['creditnote']);
				Log::create('add', $creditnote);


				Session::redirect('/sales/creditnote');
			}
		}
	}

	/**
	 * Edit an invoice
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::get();
		$creditnote = Creditnote::get_by_id($_GET['id']);

		$invoice_methods = Invoice_Method::get_all();
		$template->assign('invoice_methods', $invoice_methods);
		$template->assign('creditnote', $creditnote);
	}

	/**
	 * Download PDF
	 *
	 * @access public
	 */
	public function display_download() {
		$creditnote = Creditnote::get_by_id($_GET['id']);
		$file = $creditnote->get_pdf();
		$file->client_download();
	}

	/**
	 * Export invoices
	 *
	 * @access public
	 */
	public function display_export() {
		$template = Template::Get();

		if (isset($_POST['export_format'])) {
			$export = new $_POST['export_format']();
			$export->data = json_encode( $_POST['months'] );
			$export->save();
			$export->run();

			Session::redirect('/export?action=created');
		}
	}

	/**
	 * ajax_search
	 *
	 * @access public
	 */
	public function display_ajax_search() {
		$this->template = null;

		$pager = new Pager('invoice');
		$pager->add_sort_permission('number');
		$pager->set_direction('DESC');
		$pager->set_search($_GET['search'], [ 'number', 'price_incl', 'customer.company', 'customer.firstname' ]);
		$pager->page();

		$data = [];
		foreach ($pager->items as $invoice) {
			$name = $invoice->number . ' - ' . $invoice->customer->get_display_name() . ' - €' . $invoice->price_incl;
			$data[] = [
				'id' => $invoice->id,
				'value' => $name
			];
		}
		echo json_encode($data);
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.creditnote';
	}
}
