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

class Web_Module_Transaction extends Module {

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
	protected $template = false;

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$transactions = \Skeleton\Transaction\Transaction::get_runnable();
		foreach ($transactions as $transaction) {
			\Skeleton\Transaction\Runner::run_transaction($transaction);
			if ($transaction->failed) {
				echo $transaction->id . "\t" . $transaction->classname . ": error" . "\n";;
			} else {
				echo $transaction->id . "\t" . $transaction->classname . ": done" . "\n";;
			}
		}

	}

}
