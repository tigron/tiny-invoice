<?php
/**
 * Export_Expertm_User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Export_Excel_Invoice extends Export_Expertm {

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
			$invoices[$id] = Invoice::get_by_id($id);
		}


		$excel = new PHPExcel();
		$headers = ['Invoice number', 'Created', 'Customer', 'Price', 'Paid'];

		$worksheet = $excel->getActiveSheet();

		foreach($headers as $key => $header) {
			$worksheet->setCellValueByColumnAndRow($key, 1, $header);
		}


		$row = 1;
		foreach ($invoices as $invoice) {
			$row++;

			$worksheet->setCellValueByColumnAndRow(0, $row, $invoice->id);
			$worksheet->setCellValueByColumnAndRow(1, $row, $invoice->created);
			$worksheet->setCellValueByColumnAndRow(2, $row, $invoice->customer->get_display_name());
			$worksheet->setCellValueByColumnAndRow(3, $row, $invoice->price_incl);
			if ($invoice->paid) {
				$worksheet->setCellValueByColumnAndRow(4, $row, 'Yes');
			} else {
				$worksheet->setCellValueByColumnAndRow(4, $row, 'No');
			}
		}


		$writer = new PHPExcel_Writer_Excel2007($excel);
		ob_start();
		$writer->save("php://output");
		$content = ob_get_clean();

		$file = \Skeleton\File\File::store('excel_invoices_' . date('Ymd') . '.xlsx', $content);
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
