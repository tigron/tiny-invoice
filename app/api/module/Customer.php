<?php
/**
 * Module Customer
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Api\Module;

class Customer extends \Skeleton\Package\Api\Web\Module\Call {

	/**
	 * Get by id
	 *
	 * Get a customer by his ID
	 *
	 * @access public
	 * @param int $id
	 * @return array $customer
	 */
	public function call_getById() {
		$customer = \Customer::get_by_uuid($_REQUEST['id']);
		return $customer->get_info();
	}

	/**
	 * Get all
	 *
	 * Get the ids of all customers
	 *
	 * @access public
	 * @return array $customer_ids
	 */
	public function call_getAll() {
		$customers = \Customer::get_all();
		$customer_ids = [];
		foreach ($customers as $customer) {
			$customer_ids[] = $customer->uuid;
		}
		return $customer_ids;
	}
}
