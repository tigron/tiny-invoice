<?php
/**
 * Support_Topic module
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

 use \Skeleton\Core\Web\Module;
 use \Skeleton\Core\Web\Template;
 use \Skeleton\Core\Web\Session;
 use \Skeleton\Pager\Web\Pager;

class Web_Module_Sales_Invoice_Queue_Recurring extends Module {

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'sales/invoice/queue/recurring.twig';

	/**
	 * List
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();

		$pager = new Pager('invoice_queue_recurring_group');
        $pager->add_condition('archived', '=', '0000-00-00 00:00:00');
        $pager->add_sort_permission('id');
		$pager->add_sort_permission('created');
		$pager->add_sort_permission('next_run');
		$pager->add_sort_permission('stop_after');
		$pager->add_sort_permission('name');

		if (isset($_POST['search'])) {
			$search_fields = [
				'invoice_queue_recurring_group.id',
				'invoice_queue_recurring_group.name',
				'customer.company',
				'customer.lastname',
				'customer.firstname'
			];
			$pager->set_search($_POST['search'], $search_fields);
		}

		$pager->page();

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/sales/invoice/queue/recurring');
		}

		$template->assign('pager', $pager);
	}

    /**
	 * Delete an element from a group
	 *
	 * @access public
	 */
    function display_delete_queue_element() {
        $invoice_queue_recurring = Invoice_Queue_Recurring::get_by_id($_GET['id']);
        $invoice_queue_recurring->archive();

		Session::set_sticky('message', 'removed');
		Session::redirect('/sales/invoice/queue/recurring?action=edit&id=' . $invoice_queue_recurring->invoice_queue_recurring_group_id);
    }

    /**
	 * Add step 3
	 *
	 * @access public
	 */
	public function display_add_step3() {
        $template = Template::Get();

		if (isset($_POST['invoice_queue_recurring_group'])) {
			$_SESSION['invoice_queue_recurring']->load_array($_POST['invoice_queue_recurring_group']);
			$_SESSION['invoice_queue_recurring']->validate($errors);
			if (count($errors) > 0) {
				$template->assign('errors', $errors);
			} else {
				$_SESSION['invoice_queue_recurring']->save();
				$id = $_SESSION['invoice_queue_recurring']->id;
				unset($_SESSION['invoice_queue_recurring']);
				Session::redirect('/sales/invoice/queue/recurring?action=edit_group&id=' . $id);
			}
		}
    }

    /**
	 * Add step 2
	 *
	 * @access public
	 */
	public function display_add_step2() {
		$template = Template::get();

		if (isset($_POST['customer_contact_id'])) {
			if ($_POST['customer_contact_id'] == '') {
				$template->assign('errors', 'select_customer_contact');
			} else {
				$_SESSION['invoice_queue_recurring']->customer_contact_id = $_POST['customer_contact_id'];
				Session::redirect('/sales/invoice/queue/recurring?action=add_step3');
			}
		}

		$customer_contacts = $_SESSION['invoice_queue_recurring']->customer->get_active_customer_contacts();

		$template->assign('customer_contacts', $customer_contacts);
		$template->assign('customer', $_SESSION['invoice_queue_recurring']->customer);
		$template->assign('languages', Language::get_all());
		$template->assign('countries', Country::get_grouped());
		$template->assign('action', 'add_step2');
	}

    /**
	 * Add step 1
	 *
	 * @access public
	 */
	public function display_add_step1() {
        $template = Template::Get();

        if (isset($_POST['customer_id'])) {
			if ($_POST['customer_id']  == '') {
				$template->assign('message', 'no_customer_selected');
			} else {
				$_SESSION['invoice_queue_recurring'] = new Invoice_Queue_Recurring_Group();
				$_SESSION['invoice_queue_recurring']->customer_id = $_POST['customer_id'];
				Session::redirect('/sales/invoice/queue/recurring?action=add_step2');
			}
		}
	}

    /**
	 * Delete
	 *
	 * @access public
	 */
	public function display_delete() {
		if (!isset($_GET['id'])) {
			Session::redirect('/sales/invoice/queue/recurring');
		}

        $invoice_queue = Invoice_Queue_Recurring_Group::get_by_id($_GET['id']);
        $invoice_queue->archive();
        Session::redirect('/sales/invoice/queue/recurring');
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::get();
		$invoice_queue_recurring_group = Invoice_Queue_Recurring_Group::get_by_id($_GET['id']);

		$updated = false;

		if (isset($_POST['invoice_queue_recurring_group'])) {
			if (isset($_POST['invoice_queue_recurring_group']['run_forever'])) {
				$_POST['invoice_queue_recurring_group']['stop_after'] = null;
			}
			$invoice_queue_recurring_group->load_array($_POST['invoice_queue_recurring_group']);

			if (isset($_POST['invoice_queue_recurring_group']['direct_invoice'])) {
				$invoice_queue_recurring_group->direct_invoice = true;
				$invoice_queue_recurring_group->direct_invoice_expiration_period = $_POST['invoice_queue_recurring_group']['direct_invoice_expiration_period'];
				if (isset($_POST['invoice_queue_recurring_group']['direct_invoice_send_invoice'])) {
					$invoice_queue_recurring_group->direct_invoice_send_invoice = true;
				} else {
					$invoice_queue_recurring_group->direct_invoice_send_invoice = false;
				}
				$invoice_queue_recurring_group->direct_invoice_reference = $_POST['invoice_queue_recurring_group']['direct_invoice_reference'];
			} else {
				$invoice_queue_recurring_group->direct_invoice = false;
				$invoice_queue_recurring_group->direct_invoice_expiration_period = '';
				$invoice_queue_recurring_group->direct_invoice_send_invoice = false;
				$invoice_queue_recurring_group->direct_invoice_reference = '';
			}

			$invoice_queue_recurring_group->save();
			$updated = true;
		}

		if (isset($_POST['queue_item'])) {
			foreach ($_POST['queue_item']['description'] as $key => $value) {
				$invoice_queue_recurring = new Invoice_Queue_Recurring();
				$invoice_queue_recurring->product_type_id = $_POST['queue_item']['product_type_id'][$key];
				$invoice_queue_recurring->description = $_POST['queue_item']['description'][$key];
				$invoice_queue_recurring->vat_rate_id = $_POST['queue_item']['vat_rate_id'][$key];


				if ($invoice_queue_recurring->vat_rate_id != 0) {
					$vat_rate_country = Vat_Rate_Country::get_by_vat_rate_country($invoice_queue_recurring->vat_rate, $invoice_queue_recurring_group->customer_contact->country);
					$invoice_queue_recurring->vat_rate_id = $vat_rate_country->vat_rate_id;
					$invoice_queue_recurring->vat_rate_value = $vat_rate_country->vat;
				} else {
					$invoice_queue_recurring->vat_rate_id = null;
					$invoice_queue_recurring->vat_rate_value = 0;
				}

				$invoice_queue_recurring->qty = $_POST['queue_item']['qty'][$key];
				$invoice_queue_recurring->price = $_POST['queue_item']['price'][$key];
				$invoice_queue_recurring->invoice_queue_recurring_group_id = $invoice_queue_recurring_group->id;
				$invoice_queue_recurring->save();
			}
			$updated = true;
		}

		if (isset($_POST['existing_queue_item'])) {
			foreach ($_POST['existing_queue_item'] as $invoice_queue_recurring_id => $data) {
				$invoice_queue_recurring = Invoice_Queue_recurring::get_by_id($invoice_queue_recurring_id);
				$invoice_queue_recurring->load_array($data);

				if ($invoice_queue_recurring->vat_rate_id != 0) {
					$vat_rate_country = Vat_Rate_Country::get_by_vat_rate_country($invoice_queue_recurring->vat_rate, $invoice_queue_recurring_group->customer_contact->country);
					$invoice_queue_recurring->vat_rate_id = $vat_rate_country->vat_rate_id;
					$invoice_queue_recurring->vat_rate_value = $vat_rate_country->vat;
				} else {
					$invoice_queue_recurring->vat_rate_id = null;
					$invoice_queue_recurring->vat_rate_value = 0;
				}

				if ($invoice_queue_recurring->is_dirty()) {
					$updated = true;
				}
				$invoice_queue_recurring->save();
			}
		}

		if ($updated) {
			Session::set_sticky('message', 'updated');
			Session::redirect('/sales/invoice/queue/recurring?action=edit&id=' . $invoice_queue_recurring_group->id);
		}

		$template->assign('invoice_queue_recurring_group', $invoice_queue_recurring_group);
		$template->assign('product_types', Product_Type::get_all('name'));
		if ($invoice_queue_recurring_group->customer_contact->vat != '') {
			$template->assign('vat_rates', Vat_Rate_Country::get_by_country(Country::get_by_id(Setting::get_by_name('country_id')->value)));
		} else {
			$template->assign('vat_rates', Vat_Rate_Country::get_by_country($_SESSION['invoice']->customer_contact->country));
		}


	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.invoice_queue_recurring';
	}
}
