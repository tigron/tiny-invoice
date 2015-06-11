<?php
/**
 * Web Module Administrative Invoice Contact
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_Administrative_Invoice_Contact extends Web_Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = true;

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = null;

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {}

	/**
	 * Add/update invoice contact
	 *
	 * @access public
	 */
	public function display_manage() {
		if (isset($_GET['id']) AND $_GET['id'] > 0) {
			$invoice_contact = Invoice_Contact::get_by_id($_GET['id']);
		} else {
			$invoice_contact = new Invoice_Contact();
		}

		$invoice_contact->load_array($_POST['invoice_contact']);
		if ($invoice_contact->validate($errors) === false) {
			echo json_encode($errors);
		} else {

			if ($invoice_contact->id !== null AND $invoice_contact->is_dirty()) {
				$invoice_contact->active = false;
				$invoice_contact->save();

				$new_invoice_contact = new Invoice_Contact();
				$new_invoice_contact->load_array($_POST['invoice_contact']);
				$new_invoice_contact->active = true;
				$new_invoice_contact->save();

				echo json_encode($new_invoice_contact->get_info());
			} else {
				$invoice_contact->active = true;
				$invoice_contact->save();

				echo json_encode($invoice_contact->get_info());
			}

		}
	}

	/**
	 * Load invoice_contact (Ajax)
	 *
	 * @access public
	 */
	public function display_load_invoice_contact() {
		$this->template = null;

		$invoice_contact = Invoice_Contact::get_by_id($_GET['id']);
		echo json_encode($invoice_contact->get_info());
	}

	/**
	 * Delete invoice_contact (Ajax)
	 *
	 * @access public
	 */
	public function display_delete() {
		$this->template = null;

		$invoice_contact = Invoice_Contact::get_by_id($_GET['id']);
		$invoice_contact->active = false;
		$invoice_contact->save();

		echo json_encode([ 'status' => 1]);
	}
}