<?php
/**
 * Module Supplier
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Api\Module;

class Supplier extends \Skeleton\Package\Api\Web\Module\Call {

	/**
	 * Get by id
	 *
	 * Get a supplier by his ID
	 *
	 * @access public
	 * @param int $id
	 * @return array $supplier
	 */
	public function call_getById() {
		$supplier = \Supplier::get_by_uuid($_REQUEST['id']);
		return $supplier->get_info();
	}

	/**
	 * Get all
	 *
	 * Get the ids of all suppliers
	 *
	 * @access public
	 * @return array $supplier_ids
	 */
	public function call_getAll() {
		$suppliers = \Supplier::get_all();
		$supplier_ids = [];
		foreach ($suppliers as $supplier) {
			$supplier_ids[] = $supplier->uuid;
		}
		return $supplier_ids;
	}
}
