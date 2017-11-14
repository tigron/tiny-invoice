<?php
/**
 * Export_Expertm_User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Pager\Pager;

class Export_Excel_Document_Invoice extends Export_Expertm {

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		\Skeleton\Pager\Config::$items_per_page = 1000;
		$pager = Pager::get_by_options_hash($this->get_data());
		$pager->add_sort_permission('id');
		$pager->add_sort_permission('document.date');
		$pager->add_sort_permission('title');
		$pager->add_sort_permission('document_incoming_invoice.paid');
		$pager->add_sort_permission('document_incoming_invoice.accounting_identifier');
		$pager->add_sort_permission('document_incoming_invoice.expiration_date');
		$pager->add_sort_permission('supplier.company');
		$pager->add_sort_permission('document_incoming_invoice.price_incl');
		$pager->page();
		$invoices = $pager->items;

		$excel = new PHPExcel();
		$headers = ['Document number', 'Created', 'Supplier', 'Title', 'Price excl', 'Price incl', 'Paid'];

		$worksheet = $excel->getActiveSheet();

		foreach($headers as $key => $header) {
			$worksheet->setCellValueByColumnAndRow($key, 1, $header);
		}

		$row = 1;
		foreach ($invoices as $invoice) {
			$row++;

			$worksheet->setCellValueByColumnAndRow(0, $row, $invoice->id);
			$worksheet->setCellValueByColumnAndRow(1, $row, $invoice->created);
			$worksheet->setCellValueByColumnAndRow(2, $row, $invoice->supplier->company);
			$worksheet->setCellValueByColumnAndRow(3, $row, $invoice->title);
			$worksheet->setCellValueByColumnAndRow(4, $row, $invoice->price_excl);
			$worksheet->setCellValueByColumnAndRow(5, $row, $invoice->price_incl);
			if ($invoice->paid) {
				$worksheet->setCellValueByColumnAndRow(6, $row, 'Yes');
			} else {
				$worksheet->setCellValueByColumnAndRow(6, $row, 'No');
			}
		}

		$writer = new PHPExcel_Writer_Excel2007($excel);
		ob_start();
		$writer->save("php://output");
		$content = ob_get_clean();

		$file = \Skeleton\File\File::store('excel_incoming_invoices_' . date('Ymd') . '.xlsx', $content);
 		$this->file_id = $file->id;
		$this->save();

		return $file;
	}


	/**
	 * Writes the headings for the Excel sheet
	 *
	 * @access private
	 * @params array The names of the columns
	 */
	private function write_headers($headers = array()) {
		$worksheet = $this->excel->getActiveSheet();

		foreach($headers as $key => $header) {
			$worksheet->setCellValueByColumnAndRow($key, 1, $header);
		}
	}

}
