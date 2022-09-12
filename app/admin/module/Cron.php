<?php
/**
 * Module Cron
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module;

use \Skeleton\Core\Application\Web\Module;

class Cron extends Module {
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
		$transactions = \Skeleton\Transaction\Transaction::get_runnable();
		if (count($transactions) == 0) {
			return;
		}

		$transaction = array_shift($transactions);
		echo \Skeleton\Transaction\Runner::run_transaction($transaction);		
	}
}
