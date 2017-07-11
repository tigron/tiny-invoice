<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Financial_Account_Transaction extends Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'financial/account/transaction.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();

		$bank_account = Bank_Account::get_by_id($_GET['id']);
		$template->assign('bank_account', $bank_account);

		$pager = new Pager('bank_account_statement_transaction');
		$pager->add_sort_permission('id');
		$pager->add_sort_permission('bank_account_statement.id');
		$pager->add_sort_permission('bank_account_statement_transaction.date');
		$pager->add_sort_permission('amount');
		$pager->add_sort_permission('other_account_name');
		$pager->add_sort_permission('message');

		if (isset($_POST['bank_account_statement']) AND $_POST['bank_account_statement'] > 0) {
			$pager->add_condition('bank_account_statement_id', $_POST['bank_account_statement']);
		}

		if (isset($_POST['balanced']) and $_POST['balanced'] > -1) {
			if ($_POST['balanced'] == 0) {
				$pager->add_condition('bank_account_statement_transaction.balanced', 0);
			} else {
				$pager->add_condition('bank_account_statement_transaction.balanced', 1);
			}
		} else {
			$pager->clear_condition('balanced');
		}

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/financial/account/transaction?id=' . $bank_account->id . '&q=' . $pager->create_options_hash());
		}

		$template = Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Search invoices
	 *
	 * @access public
	 */
	public function display_search_outgoing_invoices() {
		\Skeleton\Pager\Config::$items_per_page = 10;
		$pager = new Pager('invoice');
		$pager->add_sort_permission('id');
		$pager->add_sort_permission('number');
		$pager->add_sort_permission('created');
		$pager->add_sort_permission('customer.lastname');
		$pager->add_sort_permission('customer.company');
		$pager->add_sort_permission('price_incl');
		$pager->add_sort_permission('price_excl');
		$pager->add_sort_permission('paid');


		if (isset($_GET['search'])) {
			$pager->set_search($_GET['search']);
		}
		$pager->page();
		$template = Template::get();
		$template->assign('pager', $pager);

		$transaction = Bank_Account_Statement_Transaction::get_by_id($_GET['transaction_id']);
		$template->assign('transaction', $transaction);
		$this->template = 'financial/account/transaction/search_outgoing_invoices.twig';
	}

	/**
	 * Search invoices
	 *
	 * @access public
	 */
	public function display_search_incoming_invoices() {
		\Skeleton\Pager\Config::$items_per_page = 10;
		$pager = new Pager('document');

		if (isset($_GET['search'])) {
			$pager->set_search($_GET['search']);
		}

		// Fix for pager
		$pager->add_condition('document_incoming_invoice.document_id', '>', 0);
		$pager->add_condition('classname', 'Document_Incoming_Invoice');
		$pager->add_join('document_incoming_invoice', 'document_id', 'document.id');
		$pager->add_join('supplier', 'id', 'document_incoming_invoice.supplier_id');

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('date');
		$pager->add_sort_permission('title');
		$pager->add_sort_permission('paid');
		$pager->add_sort_permission('document_incoming_invoice.accounting_identifier');
		$pager->add_sort_permission('document_incoming_invoice.expiration_date');
		$pager->add_sort_permission('supplier.company');
		$pager->add_sort_permission('document_incoming_invoice.price_incl');

		$pager->set_sort('date');
		$pager->set_direction('DESC');
		$pager->page();
		$template = Template::get();
		$template->assign('pager', $pager);

		$transaction = Bank_Account_Statement_Transaction::get_by_id($_GET['transaction_id']);
		$template->assign('transaction', $transaction);
		$this->template = 'financial/account/transaction/search_incoming_invoices.twig';
	}

	/**
	 * Search invoices
	 *
	 * @access public
	 */
	public function display_search_bookkeeping_accounts() {
		\Skeleton\Pager\Config::$items_per_page = 10;
		$pager = new Pager('bookkeeping_account');

		if (isset($_GET['search'])) {
			$pager->set_search($_GET['search']);
		}

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('number');
		$pager->add_sort_permission('name');

		$pager->set_sort('number');
		$pager->set_direction('DESC');
		$pager->page();
		$template = Template::get();
		$template->assign('pager', $pager);

		$transaction = Bank_Account_Statement_Transaction::get_by_id($_GET['transaction_id']);
		$template->assign('transaction', $transaction);
		$this->template = 'financial/account/transaction/search_bookkeeping_accounts.twig';

	}

	/**
	 * Batch link
	 *
	 * @access public
	 */
	public function display_batch_link() {
		if (isset($_POST['classname']) and $_POST['classname'] != 'ignore') {
			$transaction = Bank_Account_Statement_Transaction::get_by_id($_POST['transaction_id']);
			$classname = $_POST['classname'];
			$class = $classname::get_by_id($_POST['object_id']);
			$transaction->add_link($class, $transaction->amount);
			$transaction->apply_links();
			Session::Redirect('/financial/account/transaction?action=batch_link?start=' . $transaction->id);
		}

		if (isset($_GET['start'])) {
			$transaction = Bank_Account_Statement_Transaction::get_oldest_unbalanced($_GET['start']);
		} else {
			$transaction = Bank_Account_Statement_Transaction::get_oldest_unbalanced();
		}
		$template = Template::get();
		$template->assign('transaction', $transaction);


	}

	/**
	 * Batch link
	 *
	 * @access public
	 */
	public function display_automatic_link() {
		$template = Template::get();
		$transactions = Bank_Account_Statement_Transaction::get_unbalanced();
		$template->assign('transactions', $transactions);
	}

	/**
	 * Link a transaction (AJAX)
	 *
	 * @access public
	 */
	public function display_automatic_link_transaction() {
		$this->template = false;
		$transaction = Bank_Account_Statement_Transaction::get_by_id($_POST['id']);
		$response = [
			'id' => $transaction->id,
			'success' => false,
			'message' => ''
		];

		try {
			$transaction->automatic_link();
			$response['success'] = true;
		} catch (Exception $e) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}

		echo json_encode($response);
	}

	/**
	 * Link invoice
	 *
	 * @access public
	 */
	public function display_link_outgoing_invoice() {
		$transaction = Bank_Account_Statement_Transaction::get_by_id($_POST['transaction_id']);
		$invoice = Invoice::get_by_id($_POST['invoice_id']);
		$transaction->link_invoice($invoice, $_POST['link_invoice_amount']);

		if (isset($_POST['link_customer_amount']) and $_POST['link_customer_amount'] != 0) {
			$transaction->link_customer_contact($invoice->customer_contact, $_POST['link_customer_amount']);
		}
		Session::redirect('/financial/account/transaction?action=edit&id=' . $transaction->id);
	}

	/**
	 * Link document
	 *
	 * @access public
	 */
	public function display_link_incoming_invoice() {
		$transaction = Bank_Account_Statement_Transaction::get_by_id($_POST['transaction_id']);
		$document = Document::get_by_id($_POST['document_id']);
		$transaction->link_document($document, $_POST['link_incoming_invoice_amount']*-1);

		if (isset($_POST['link_supplier_amount']) and $_POST['link_supplier_amount'] != 0) {
			$transaction->link_supplier($document->supplier, $_POST['link_supplier_amount']*-1);
		}
		Session::redirect('/financial/account/transaction?action=edit&id=' . $transaction->id);
	}

	/**
	 * Link bookkeeping_account
	 *
	 * @access public
	 */
	public function display_link_bookkeeping_account() {
		$transaction = Bank_Account_Statement_Transaction::get_by_id($_POST['transaction_id']);
		$bookkeeping_account = Bookkeeping_Account::get_by_id($_POST['bookkeeping_account_id']);
		$transaction->link_bookkeeping_account($bookkeeping_account, $_POST['link_bookkeeping_account_amount']);

		Session::redirect('/financial/account/transaction?action=edit&id=' . $transaction->id);
	}

	/**
	 * Edit a transaction
	 *
	 * @access public
	 */
	public function display_edit() {
		$transaction = Bank_Account_Statement_Transaction::get_by_id($_GET['id']);
		$template = Template::get();
		$template->assign('transaction', $transaction);
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.bookkeeping';
	}
}
