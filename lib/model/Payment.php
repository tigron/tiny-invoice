<?php
/**
 * Payment class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Payment {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Pager\Page;
	use \Skeleton\Object\Delete;

	/**
	 * Get by Payment_List
	 *
	 * @access public
	 * @param Payment_List $payment_list
	 */
	public static function get_by_payment_list(Payment_List $payment_list) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM payment WHERE payment_list_id=?', [ $payment_list->id ]);
		$payments = [];
		foreach ($ids as $id) {
			$payments[] = self::get_by_id($id);
		}
		return $payments;
	}

}
