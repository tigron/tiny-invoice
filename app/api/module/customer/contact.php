<?php
/**
 * Module Customer
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_Customer_Contact extends \Skeleton\Package\Api\Web\Module\Call {

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
		$customer_contact = Customer_Contact::get_by_id($_REQUEST['id']);
		return $customer_contact->get_info();
	}

	/**
	 * Get all
	 *
	 * Get the ids of all customers
	 *
	 * @access public
	 * @param int $id
	 * @return array $customer_ids
	 */
	public function call_getByCustomerId() {
		$customer = Customer::get_by_id($_REQUEST['id']);
		$customer_contacts = $customer->get_active_customer_contacts();

		$customer_contact_ids = [];
		foreach ($customer_contacts as $customer_contact) {
			$customer_contact_ids[] = $customer_contact->id;
		}
		return $customer_contact_ids;
	}
}
