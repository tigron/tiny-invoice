<?php
/**
 * Module Cron
 *
 * @author David Vandemaele <david@tigron.be>
 */

require_once LIB_PATH . '/model/Transaction_Runner.php';

class Module_Cron extends Web_Module {
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
	public $template = '';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {

	}

	/**
	 * Run transaction
	 *
	 * @access public
	 */
	public function display_run() {
		$trans = new Transaction_Runner();
		$trans->run();
	}
}
