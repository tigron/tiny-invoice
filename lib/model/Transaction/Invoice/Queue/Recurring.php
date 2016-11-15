<?php
/**
 * Transaction_Invoice_Queue_Recurring
 *
 * @package KNX-lib
 * @subpackage transactions
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Transaction_Invoice_Queue_Recurring extends Transaction {

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int $id
	 */
	public function __construct($id = null) {
		parent::__construct($id);
		$this->type = 'Invoice_Queue_Recurring';
	}

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		$groups = Invoice_Queue_Recurring_Group::get_runnable();
		foreach ($groups as $group) {
			$group->run();
		}
		$this->schedule('1 hour');
	}
}
