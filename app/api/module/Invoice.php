<?php
/**
 * Module Supplier
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Api\Module;

class Invoice extends \Skeleton\Package\Api\Web\Module\Call {

	/**
	 * Get by id
	 *
	 * Get an invoice by his ID
	 *
	 * @access public
	 * @param int $id
	 * @return array $invoice
	 */
	public function call_getById() {
		$invoice = \Invoice::get_by_id($_REQUEST['id']);
		return $invoice->get_info();
	}

	/**
	 * Get PDF
	 *
	 * Get the PDF document of an invoice by his ID
	 *
	 * @access public
	 * @param int $id
	 * @return array $fileinfo
	 */
	public function call_getPdf() {
		$invoice = \Invoice::get_by_id($_REQUEST['id']);

		return $invoice->file->get_info();
	}

}
