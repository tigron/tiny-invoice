<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Application\Web\Module;

class Index extends Module {

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
	protected $template = 'index.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		\Skeleton\Core\Web\Session::redirect('/sales/invoice');
	}
}
