<?php
/**
 * Export_Excel_Invoice class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class Export_Excel_Invoice extends Export {

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		$months = $this->get_data();

		$db = Database::get();
		$ids = [];
		foreach ($months as $month) {
			$ids = array_merge($ids, $db->get_column('SELECT id FROM invoice WHERE created LIKE "' . $month . '%"', [ ]));
		}

		$invoices = [];
		foreach ($ids as $id) {
			$invoices[] = Invoice::get_by_id($id);
		}

		$spreadsheet = new Spreadsheet();
		$headers = ['Invoice number', 'Created', 'Expiration Date', 'Customer', 'Price excl', 'Price incl', 'Paid'];

		$worksheet = $spreadsheet->getActiveSheet();

		foreach($headers as $key => $header) {
			$worksheet->setCellValueByColumnAndRow(($key+1), 1, $header);
		}

		$row = 1;
		foreach ($invoices as $invoice) {
			$row++;

			$worksheet->setCellValueByColumnAndRow(1, $row, $invoice->id);
			$worksheet->setCellValueByColumnAndRow(2, $row, $invoice->created);
			$worksheet->setCellValueByColumnAndRow(3, $row, $invoice->expiration_date);
			$worksheet->setCellValueByColumnAndRow(4, $row, $invoice->customer->get_display_name());
			$worksheet->setCellValueByColumnAndRow(5, $row, $invoice->price_excl);
			$worksheet->setCellValueByColumnAndRow(6, $row, $invoice->price_incl);
			if ($invoice->paid) {
				$worksheet->setCellValueByColumnAndRow(7, $row, 'Yes');
			} else {
				$worksheet->setCellValueByColumnAndRow(7, $row, 'No');
			}
		}

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		ob_start();
		$writer->save("php://output");
		$content = ob_get_clean();

		$file = \Skeleton\File\File::store('excel_invoices_' . date('Ymd') . '.xlsx', $content);
 		$this->file_id = $file->id;
		$this->save();

		return $file;
	}

}
