<?php
/**
 * Module Cron
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Module;

class Web_Module_Cron extends Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = false;

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = null;

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		\Skeleton\Transaction\Runner::run();
	}
}
