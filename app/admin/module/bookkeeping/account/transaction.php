<?php
/**
 * Web Module Administrative Bookkeeping_Account
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Bookkeeping_Account_Transaction extends Module {
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
	protected $template = 'bookkeeping/account/transaction.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();

		$bookkeeping_account = Bookkeeping_Account::get_by_id($_GET['id']);

		$pager = new Pager('bank_account_statement_transaction_balance');
		$pager->add_condition('linked_object_classname', 'Bookkeeping_Account');
		$pager->add_condition('linked_object_id', $bookkeeping_account->id);
		$pager->add_sort_permission('bank_account_statement_transaction.date');
		$pager->add_sort_permission('bank_account_statement_transaction.amount');
		$pager->add_sort_permission('amount');
		$pager->add_sort_permission('bank_account_statement_transaction.other_account_name');
		$pager->add_sort_permission('bank_account_statement_transaction.message');
		$pager->set_sort('bank_account_statement_transaction.date');
		$pager->set_direction('DESC');
		$pager->page();

		$template->assign('pager', $pager);


		$template->assign('bookkeeping_account', $bookkeeping_account);

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
