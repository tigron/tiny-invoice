<?php
/**
 * Export_Excel_Document_Creditnote class
 *
 * @author Hassan Ahmed <hassan.ahmed@tigron.be>
 */

use \Skeleton\Pager\Pager;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class Export_Excel_Document_CreditNote extends Export_Expertm {

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
		$pager->add_sort_permission('document_incoming_creditnote.paid');
		$pager->add_sort_permission('document_incoming_creditnote.accounting_identifier');
		$pager->add_sort_permission('document_incoming_creditnote.expiration_date');
		$pager->add_sort_permission('supplier.company');
		$pager->add_sort_permission('document_incoming_creditnote.price_incl');
		$pager->set_page(1);
		$pager->page();

		$creditnotes = $pager->items;

		$spreadsheet = new Spreadsheet();
		$headers = ['Document number', 'Date', 'Accounting identifier', 'Expiration Date', 'Supplier', 'Title', 'Payment message', 'Price excl', 'Price incl', 'Paid', 'Tags'];

		$worksheet = $spreadsheet->getActiveSheet();

		foreach($headers as $key => $header) {
			$worksheet->setCellValueByColumnAndRow(($key+1), 1, $header);
		}

		$row = 1;
		foreach ($creditnotes as $creditnote) {
			$row++;

			$worksheet->setCellValueByColumnAndRow(1, $row, $creditnote->id);
			$worksheet->setCellValueByColumnAndRow(2, $row, $creditnote->date);
			$worksheet->setCellValueByColumnAndRow(3, $row, $creditnote->accounting_identifier);
			$worksheet->setCellValueByColumnAndRow(4, $row, $creditnote->expiration_date);
			$worksheet->setCellValueByColumnAndRow(5, $row, $creditnote->supplier->company);
			$worksheet->setCellValueByColumnAndRow(6, $row, $creditnote->title);

			if (!empty($creditnote->payment_structured_message)) {
				$worksheet->setCellValueByColumnAndRow(7, $row, '+++' . $creditnote->payment_structured_message . '+++');
			} else {
				$worksheet->setCellValueByColumnAndRow(7, $row, $creditnote->payment_message);
			}

			$worksheet->setCellValueByColumnAndRow(8, $row, $creditnote->price_excl);
			$worksheet->setCellValueByColumnAndRow(9, $row, $creditnote->price_incl);

			if ($creditnote->paid) {
				$worksheet->setCellValueByColumnAndRow(10, $row, 'Yes');
			} else {
				$worksheet->setCellValueByColumnAndRow(10, $row, 'No');
			}

			$tags = $creditnote->get_tags();
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

		$file = \Skeleton\File\File::store('excel_incoming_creditnotes_' . date('Ymd') . '.xlsx', $content);
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
