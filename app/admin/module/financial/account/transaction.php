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
		$pager->add_sort_permission('date');
		$pager->add_sort_permission('amount');
		$pager->add_sort_permission('other_account_name');
		$pager->add_sort_permission('message');

		if (isset($_POST['bank_account_statement']) AND $_POST['bank_account_statement'] > 0) {
			$pager->add_condition('bank_account_statement_id', $_POST['bank_account_statement']);
		}

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		$template = Template::Get();
		$template->assign('pager', $pager);
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


}
