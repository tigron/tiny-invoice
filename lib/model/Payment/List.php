<?php
/**
 * Payment_List class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Payment_List {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Pager\Page;
	use \Skeleton\Object\Delete;

	/**
	 * Get payments
	 *
	 * @access public
	 * @return array $payments
	 */
	public function get_payments() {
		return Payment::get_by_payment_list($this);
	}

}
