<?php
/**
 * Module Credit Note
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_Credit_Note extends \Skeleton\Package\Api\Web\Module\Call {

	/**
	 * Get by id
	 *
	 * Get a credit note by his ID
	 *
	 * @access public
	 * @param int $id
	 * @return array $invoice
	 */
	public function call_getById() {
		$credit_note = Creditnote::get_by_id($_REQUEST['id']);
		return $credit_note->get_info();
	}

	/**
	 * Get PDF
	 *
	 * Get the PDF document of a credit note by his ID
	 *
	 * @access public
	 * @param int $id
	 * @return array $fileinfo
	 */
	public function call_getPdf() {
		$credit_note = Creditnote::get_by_id($_REQUEST['id']);
		return $credit_note->file->get_info();
	}

}
