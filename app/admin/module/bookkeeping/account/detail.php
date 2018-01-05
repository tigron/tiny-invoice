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

class Web_Module_Bookkeeping_Account_Detail extends Module {
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
	protected $template = 'bookkeeping/account/detail.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();
		$bookkeeping_account = Bookkeeping_Account::get_by_id($_GET['id']);

		if (isset($_POST['bookkeeping_account'])) {
			$bookkeeping_account->load_array($_POST['bookkeeping_account']);
			if ($bookkeeping_account->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$bookkeeping_account->save();

				Session::set_sticky('message', 'updated');
				Session::redirect('/bookkeeping/account/detail?id=' . $bookkeeping_account->id);
			}
		}

		$template->assign('bookkeeping_account', $bookkeeping_account);
		$template->assign('countries', Country::get_grouped());
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
