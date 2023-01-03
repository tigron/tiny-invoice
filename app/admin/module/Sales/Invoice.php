<?php
/**
 * Web Module Administrative Invoice
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Sales;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Application\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Invoice extends Module {
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
	protected $template = 'sales/invoice.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$pager = new Pager('invoice');

		$pager->add_sort_permission('number');
		$pager->add_sort_permission('created');
		$pager->add_sort_permission('customer.lastname');
		$pager->add_sort_permission('customer.company');
		$pager->add_sort_permission('price_incl');
		$pager->add_sort_permission('price_excl');
		$pager->add_sort_permission('paid');
		$pager->add_join('invoice_item', 'invoice_id', 'invoice.id');
		$pager->set_direction('desc');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}

		if (isset($_POST['date']) AND !empty($_POST['date'])) {
			list($date_start, $date_end) = explode(' to ', $_POST['date']);
			$pager->add_condition('created', '>=', date('Y-m-d H:i:s', strtotime($date_start)));
			$pager->add_condition('created', '<=', date('Y-m-d H:i:s', strtotime($date_end)));
		}

		if (isset($_POST['paid']) AND $_POST['paid'] != '') {
			$pager->add_condition('paid', $_POST['paid']);
		}

		$pager->page();

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/sales/invoice');
		}

		$template = Template::get();
		$template->assign('pager', $pager);
	}

	/**
	 * Create an invoice: select customer
	 *
	 * @access public
	 */
	public function display_create_step1() {
		$template = Template::get();

		if (!isset($_SESSION['invoice'])) {
			$_SESSION['invoice'] = new \Invoice();
			$_SESSION['invoice']->load_array([ 'customer_id' => 0, 'customer_contact_id' => 0 ]);
		}

		if (isset($_GET['customer_id']) AND isset($_GET['customer_contact_id'])) {
			$_SESSION['invoice']->customer_id = $_GET['customer_id'];
			$_SESSION['invoice']->customer_contact_id = $_GET['customer_contact_id'];
			$_SESSION['invoice']->reference = $_SESSION['invoice']->customer_contact->reference;
			Session::redirect('/sales/invoice?action=create_step3');
		}

		if (isset($_POST['customer_id'])) {
			if ($_POST['customer_id'] == '') {
				$template->assign('errors', 'select_customer');
			} else {
				$_SESSION['invoice']->customer_id = $_POST['customer_id'];
				Session::redirect('/sales/invoice?action=create_step2');
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
		if (!isset($_SESSION['invoice'])) {
			Session::redirect('/sales/invoice?action=create_step1');
		}
		print_r($_POST);
		$template = Template::get();
		if (isset($_POST['customer_contact_id'])) {
			if ($_POST['customer_contact_id'] == '') {
				$template->assign('errors', 'select_customer_contact');
			} else {
				$_SESSION['invoice']->customer_contact_id = $_POST['customer_contact_id'];
				$_SESSION['invoice']->reference = $_SESSION['invoice']->customer_contact->reference;
				Session::redirect('/sales/invoice?action=create_step3');
			}
		}

		$customer_contacts = $_SESSION['invoice']->customer->get_active_customer_contacts();
		$template->assign('customer_contacts', $customer_contacts);
		$template->assign('customer', $_SESSION['invoice']->customer);
		$template->assign('languages', \Language::get_all());
		$template->assign('countries', \Country::get_grouped());
		$template->assign('action', 'create_step2');
	}

	/**
	 * Create an invoice: add invoice items
	 *
	 * @access public
	 */
	public function display_create_step3() {
		$template = Template::get();

		if (isset($_POST['invoice_item']) or isset($_POST['invoice'])) {
			if (!isset($_POST['invoice_item'])) {
				$_POST['invoice_item'] = [];
			}
			$errors = [];
			$total_price = 0;
			$invoice_items = [];

			foreach ($_POST['invoice_item'] as $row => $item) {
				$invoice_item = new \Invoice_Item();
				if (trim($item['invoice_queue_id']) == '') {
					unset($item['invoice_queue_id']);
				}

				$invoice_item->load_array($item);
				if ($_POST['invoice']['service_delivery_to_country_id'] == \Setting::get_by_name('country_id')->value) {
					// Vat in country of company
					if ($item['company_vat_rate_id'] != 0) {
						$vat_rate_country = \Vat_Rate_Country::get_by_id($item['company_vat_rate_id']);
						$invoice_item->vat_rate_id = $vat_rate_country->vat_rate_id;
						$invoice_item->vat_rate_value = $vat_rate_country->vat;
					} else {
						$invoice_item->vat_rate_id = null;
						$invoice_item->vat_rate_value = 0;
					}
				} elseif ($_POST['invoice']['service_delivery_to_country_id'] > 0) {
					$delivery_country = \Country::get_by_id($_POST['invoice']['service_delivery_to_country_id']);
					if ($delivery_country->european) {
						if (!$_SESSION['invoice']->customer_contact->vat_bound()) {
							// No vat
							$invoice_item->vat_rate_id = null;
							$invoice_item->vat_rate_value = 0;
						} else {
							if ($item['customer_vat_rate_id'] != 0) {
								$vat_rate_country = \Vat_Rate_Country::get_by_id($item['customer_vat_rate_id']);
								$invoice_item->vat_rate_id = $vat_rate_country->vat_rate_id;
								$invoice_item->vat_rate_value = $vat_rate_country->vat;
							} else {
								$invoice_item->vat_rate_id = null;
								$invoice_item->vat_rate_value = 0;
							}
						}
					} else {
						$invoice_item->vat_rate_id = null;
						$invoice_item->vat_rate_value = 0;
					}
				} else {
					$invoice_item->vat_rate_id = null;
					$invoice_item->vat_rate_value = 0;
				}

				if ($_POST['invoice']['vat_mode'] == 'group') {
					$invoice_item->price_excl = $invoice_item->price;
				} else {
					$invoice_item->price_incl = $invoice_item->price;
				}


				try{
					$invoice_item->calculate_prices(false);
					$total_price += $invoice_item->get_price_excl();
				} catch (\TypeError $e) {}

				if ($invoice_item->validate($item_errors) === false) {
					$errors[$row] = $item_errors;
				} else {
					$invoice_items[] = $invoice_item;
				}
			}

			if (count($errors) > 0 || $total_price == 0) {
				if (count($errors)) {
					$template->assign('errors', $errors);
				}

				if ($total_price == 0) {
					$template->assign('total_price_error', true);
				}

				$_SESSION['invoice']->reference = $_POST['invoice']['reference'];
				$_SESSION['invoice']->internal_reference = $_POST['invoice']['internal_reference'];
			} else {
				$invoice = $_SESSION['invoice'];
				$invoice->expiration_date = date('YmdHis', strtotime($_POST['invoice']['expiration_date']));
				$invoice->reference = $_POST['invoice']['reference'];
				$invoice->internal_reference = $_POST['invoice']['internal_reference'];
				$invoice->vat_mode = $_POST['invoice']['vat_mode'];
				$invoice->generate_number();

				$invoice_errors = [];
				if ($invoice->validate($invoice_errors) === false){
					$template->assign('invoice_errors', $invoice_errors);
				} else {
					$invoice->expiration_date = date('YmdHis', strtotime($_POST['invoice']['expiration_date']));
					$invoice->save();

					foreach ($invoice_items as $invoice_item) {
						$invoice->add_invoice_item($invoice_item);
						if (!empty($invoice_item->invoice_queue_id)) {
							$invoice_queue = \Invoice_Queue::get_by_id($invoice_item->invoice_queue_id);
							$invoice_queue->processed_to_invoice_item_id = $invoice_item->id;
							$invoice_queue->save();
						}
					}

					if (isset($_POST['send_invoice'])) {
						$invoice->schedule_send();
					}

					unset($_SESSION['invoice']);
					\Log::create('add', $invoice);

					Session::redirect('/sales/invoice');
				}
			}
		}

		$invoice_queue_items = \Invoice_Queue::get_unprocessed_by_customer_contact($_SESSION['invoice']->customer_contact);
		$template->assign('invoice_queue_items', $invoice_queue_items);
		$template->assign('customer_vat_rates', \Vat_Rate_Country::get_by_country($_SESSION['invoice']->customer_contact->country));
		$template->assign('company_vat_rates', \Vat_Rate_Country::get_by_country(\Country::get_by_id(\Setting::get_by_name('country_id')->value)));
		$template->assign('action', 'create_step3');
		$template->assign('product_types', \Product_Type::get_all('name'));
		$template->assign('settings', \Setting::get_as_array());
		$template->assign('countries', \Country::get_grouped());
	}

	/**
	 * Edit an invoice
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::get();
		$invoice = \Invoice::get_by_id($_GET['id']);

		if (isset($_POST['invoice'])) {
			// Clean checkbox
			if (isset($_POST['invoice']['send_reminder_mail']) && $_POST['invoice']['send_reminder_mail'] == 'on'){
				$_POST['invoice']['send_reminder_mail'] = true;
			} else {
				$_POST['invoice']['send_reminder_mail'] = false;
			}

			$invoice->load_array($_POST['invoice']);
			// If reference is changed
			if ($invoice->is_dirty('reference')){
				if ($invoice->paid) {
					throw new \Exception("Not able to change a paid invoice");
				}
				// Regenerate invoice
				try {
					$invoice->file->delete();
					$invoice->file_id = 0;
				} catch (\Exception $e) {
					// It could be no file is created yet
				}
				$invoice->save();
				$invoice->get_pdf();
			} else {
				$invoice->save();
			}

			Session::set_sticky('message', 'updated');
			Session::redirect('/sales/invoice?action=edit&id=' . $invoice->id);
		}

		if (isset($_POST['transfer'])) {
			$this->display_add_transfer();
		}

		$invoice_methods = \Invoice_Method::get_all();
		$template->assign('invoice_methods', $invoice_methods);
		$template->assign('invoice', $invoice);
	}

	/**
	 * Add transfer
	 *
	 * @access public
	 */
	public function display_add_transfer() {
		if (!isset($_GET['id']) || $_POST['transfer']['amount'] == '') {
			Session::redirect('/sales/invoice');
		}

		$invoice = \Invoice::get_by_id($_GET['id']);

		$transfer = new \Transfer();
		$transfer->type = TRANSFER_TYPE_PAYMENT_MANUAL;
		$transfer->amount = $_POST['transfer']['amount'];

		$invoice->add_transfer($transfer);

		Session::redirect('/sales/invoice?action=edit&id=' . $invoice->id);
	}

	/**
	 * Set reminder
	 *
	 * @access public
	 */
	public function display_set_reminder() {
		$invoice = \Invoice::get_by_id($_GET['id']);
		$invoice->send_reminder_mail = $_POST['send_reminder_mail'];
		$invoice->save();
		$this->template = false;
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
	 * Download PDF
	 *
	 * @access public
	 */
	public function display_download() {
		$invoice = \Invoice::get_by_id($_GET['id']);
		$file = $invoice->get_pdf()->client_download();
	}

	/**
	 * Email PDF
	 *
	 * @access public
	 */
	public function display_send() {
		$invoice = \Invoice::get_by_id($_GET['id']);
		$invoice_method = \Invoice_Method::get_by_id($_GET['invoice_method_id']);
		$invoice->send($invoice_method);
		Session::redirect('/sales/invoice?action=edit&id=' . $_GET['id']);
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.invoice';
	}
}
