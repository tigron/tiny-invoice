<?php
/**
 * Transaction_Runner Class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/model/Transaction.php';

class Transaction_Runner {

	/**
	 * Constructor
	 *
	 * @param NULL
	 */
	public function __construct() { }

	/**
	 * Run transaction, acquire one if none set
	 *
	 * @access private
	 * @param Transaction
	 */
	 private function run_transaction(Transaction $transaction) {
		ob_start();
		Transaction::run_transaction($transaction);
		$output = ob_get_contents();
		ob_end_clean();
		file_put_contents(TMP_PATH . '/log/transaction.log', $output, FILE_APPEND);
	}

	/**
	 * Run transactions
	 *
	 * @access public
	 */
	 public function run() {
		$transactions = Transaction::get_runnable();
		foreach ($transactions as $transaction) {
			$this->run_transaction($transaction);
		}
	}
}
