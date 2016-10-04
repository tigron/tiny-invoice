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
		$pager->add_sort_permission('name');

		if (isset($_POST['search'])) {
			$search_fields = [
				'invoice_queue_recurring_group.id',
				'invoice_queue_recurring_group.name',
			];
			$pager->set_search($_POST['search'], $search_fields);
		}

		$pager->page();
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
			print_r($errors);
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
			$invoice_queue_recurring_group->load_array($_POST['invoice_queue_recurring_group']);
			$invoice_queue_recurring_group->save();
			$updated = true;
		}

		if (isset($_POST['queue_item'])) {
			foreach ($_POST['queue_item']['description'] as $key => $value) {
				$invoice_queue_recurring = new Invoice_Queue_Recurring();
				$invoice_queue_recurring->description = $_POST['queue_item']['description'][$key];
				$invoice_queue_recurring->vat = $_POST['queue_item']['vat'][$key];
				$invoice_queue_recurring->qty = $_POST['queue_item']['qty'][$key];
				$invoice_queue_recurring->price = $_POST['queue_item']['price'][$key];
				$invoice_queue_recurring->invoice_queue_recurring_group_id = $invoice_queue_recurring_group->id;
				$invoice_queue_recurring->save();
			}
			$updated = true;
		}

		if ($updated) {
			Session::set_sticky('message', 'updated');
			Session::redirect('/sales/invoice/queue/recurring?action=edit&id=' . $invoice_queue_recurring_group->id);
		}

		$template->assign('invoice_queue_recurring_group', $invoice_queue_recurring_group);
		$template->assign('customer_contacts', $invoice_queue_recurring_group->customer->get_active_customer_contacts());
	}
}
