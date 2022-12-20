<?php
/**
 * Export_Expertm_User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Pager\Pager;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

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
		$pager->set_page(1);
		$pager->page();

		$invoices = $pager->items;



		$spreadsheet = new Spreadsheet();
		$headers = ['Document number', 'Date', 'Accounting identifier', 'Expiration Date', 'Supplier', 'Title', 'Payment message', 'Price excl', 'Price incl', 'Paid', 'Tags'];

		$worksheet = $spreadsheet->getActiveSheet();

		foreach($headers as $key => $header) {
			$worksheet->setCellValueByColumnAndRow(($key+1), 1, $header);
		}

		$row = 1;
		foreach ($invoices as $invoice) {
			$row++;

			$worksheet->setCellValueByColumnAndRow(1, $row, $invoice->id);
			$worksheet->setCellValueByColumnAndRow(2, $row, $invoice->date);
			$worksheet->setCellValueByColumnAndRow(3, $row, $invoice->accounting_identifier);
			$worksheet->setCellValueByColumnAndRow(4, $row, $invoice->expiration_date);
			$worksheet->setCellValueByColumnAndRow(5, $row, $invoice->supplier->company);
			$worksheet->setCellValueByColumnAndRow(6, $row, $invoice->title);
			if (!empty($invoice->payment_structured_message)) {
				$worksheet->setCellValueByColumnAndRow(7, $row, '+++' . $invoice->payment_structured_message . '+++');						
			} else {
				$worksheet->setCellValueByColumnAndRow(7, $row, $invoice->payment_message);
			}
			$worksheet->setCellValueByColumnAndRow(8, $row, $invoice->price_excl);
			$worksheet->setCellValueByColumnAndRow(9, $row, $invoice->price_incl);
			if ($invoice->paid) {
				$worksheet->setCellValueByColumnAndRow(10, $row, 'Yes');
			} else {
				$worksheet->setCellValueByColumnAndRow(10, $row, 'No');
			}
			$tags = $invoice->get_tags();
			$txt_tags = '';
			foreach ($tags as $tag) {
				$txt_tags .= $tag->name . ' ';
			}
			$worksheet->setCellValueByColumnAndRow(11, $row, $txt_tags);
		}

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
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
