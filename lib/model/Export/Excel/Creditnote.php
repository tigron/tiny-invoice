<?php
/**
 * Export_Excel_Creditnote class
 *
 * @author Hassan Ahmed <hassan.ahmed@tigron.be>
 */

use \Skeleton\Database\Database;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;

class Export_Excel_Creditnote extends Export {

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
			$ids = array_merge($ids, $db->get_column('SELECT id FROM creditnote WHERE created LIKE "' . $month . '%"', [ ]));
		}

		$creditnotes = [];
		foreach ($ids as $id) {
			$creditnotes[] = \Creditnote::get_by_id($id);
		}

		$spreadsheet = new Spreadsheet();
		$headers = ['Creditnote number', 'Created', 'Expiration Date', 'Customer', 'Price excl', 'Price incl', 'Paid'];

		$worksheet = $spreadsheet->getActiveSheet();

		foreach($headers as $key => $header) {
			$worksheet->setCellValueByColumnAndRow(($key+1), 1, $header);
		}

		$row = 1;
		foreach ($creditnotes as $creditnote) {
			$row++;

			$worksheet->setCellValueByColumnAndRow(1, $row, $creditnote->id);
			$worksheet->setCellValueByColumnAndRow(2, $row, $creditnote->created);
			$worksheet->setCellValueByColumnAndRow(3, $row, $creditnote->expiration_date);
			$worksheet->setCellValueByColumnAndRow(4, $row, $creditnote->customer->get_display_name());
			$worksheet->setCellValueByColumnAndRow(5, $row, $creditnote->price_excl);
			$worksheet->setCellValueByColumnAndRow(6, $row, $creditnote->price_incl);

			if ($creditnote->paid) {
				$worksheet->setCellValueByColumnAndRow(7, $row, 'Yes');
			} else {
				$worksheet->setCellValueByColumnAndRow(7, $row, 'No');
			}
		}

		$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
		ob_start();
		$writer->save("php://output");
		$content = ob_get_clean();

		$file = \Skeleton\File\File::store('excel_creditnotes_' . date('Ymd') . '.xlsx', $content);
 		$this->file_id = $file->id;
		$this->save();

		return $file;
	}
}
