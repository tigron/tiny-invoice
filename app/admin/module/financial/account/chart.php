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

class Web_Module_Financial_Account_Chart extends Module {

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
	protected $template = 'financial/account/chart.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$bank_account = Bank_Account::get_by_id($_GET['id']);
		$template = Template::get();
		$template->assign('bank_account', $bank_account);
	}


}
