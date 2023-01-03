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

class Mobile extends Module {

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
	protected $template = 'mobile/register.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {

	}
}
