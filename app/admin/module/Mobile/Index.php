<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Mobile;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Application\Web\Module;

class Index extends Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = false;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'mobile/index.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();

		if (isset($_COOKIE['mobile-token'])) {
			$mobile = null;
			try {
				$mobile = \Mobile::get_by_token($_COOKIE['mobile-token']);
			} catch (\Exception $e) { }
			
			if ($mobile !== null and $mobile->registered === null) {
				unset($_COOKIE['mobile-token']);
				$mobile = null;
			}
			
			if ($mobile === null) {
				$template->assign('registered', false);			
			} else {
				$template->assign('registered', true);
				$template->assign('mobile', $mobile);
			}
		}
	}

}
