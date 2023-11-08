<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Financial\Account;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Pager\Web\Pager;

class Chart extends Module {

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
		$bank_account = \Bank_Account::get_by_id($_GET['bank_account_id']);
		$template = Template::get();
		$template->assign('bank_account', $bank_account);
	}


}
