<?php
/**
 * Web Module Administrative Bookkeeping_Account
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Bookkeeping;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;
use \Skeleton\Pager\Web\Pager;

class Account extends Module {
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
	protected $template = 'bookkeeping/account.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();

		$pager = new Pager('bookkeeping_account');

		$pager->add_sort_permission('name');
		$pager->add_sort_permission('number');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/bookkeeping/account');
		}

		$template = Template::get();
		$template->assign('pager', $pager);
	}

	/**
	 * Add
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Template::Get();

		if (isset($_POST['bookkeeping_account'])) {
			$bookkeeping_account = new \Bookkeeping_Account();

			$bookkeeping_account->load_array($_POST['bookkeeping_account']);
			if ($bookkeeping_account->validate($errors) === false) {
				print_r($errors);
				$template->assign('errors', $errors);
			} else {
				$bookkeeping_account->save();

				Session::set_sticky('message', 'created');
				Session::redirect('/bookkeeping/account');
			}
		}

		$template->assign('countries', \Country::get_grouped());
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::Get();
		$bookkeeping_account = \Bookkeeping_Account::get_by_id($_GET['id']);

		if (isset($_POST['bookkeeping_account'])) {
			$bookkeeping_account->load_array($_POST['bookkeeping_account']);
			if ($bookkeeping_account->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$bookkeeping_account->save();

				Session::set_sticky('message', 'updated');
				Session::redirect('/bookkeeping/account?action=edit&id=' . $bookkeeping_account->id);
			}
		}

		$template->assign('bookkeeping_account', $bookkeeping_account);
		$template->assign('countries', \Country::get_grouped());
	}

	/**
	 * Search bookkeeping_account (ajax)
	 *
	 * @access public
	 */
	public function display_ajax_search() {
		$this->template = null;

		$pager = new Pager('bookkeeping_account');
		$pager->add_sort_permission('company');
		$pager->set_search($_GET['search']);
		$pager->page();

		$data = [];
		foreach ($pager->items as $bookkeeping_account) {
			$name = $bookkeeping_account->company;
			$data[] = [
				'id' => $bookkeeping_account->id,
				'value' => $name,
				'iban' => $bookkeeping_account->iban
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
		return 'admin.bookkeeping';
	}

}
