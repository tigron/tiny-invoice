<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Json\Bank;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;

class Account extends Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = false;

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$bank_account = \Bank_Account::get_by_id($_GET['id']);

		$statements = \Bank_Account_Statement::get_by_bank_account_with_dynamic_resolution($bank_account, 1000);

		$data = [];
		foreach ($statements as $statement) {
			$row = [
				'x' => floatval(strtotime($statement->original_situation_date) . '000') + 86400000,
				'y' => floatval($statement->original_situation_balance),
				'id' => $statement->sequence
			];
			$data[] = $row;
		}

		echo json_encode($data, JSON_PRETTY_PRINT);
	}

	/**
	 * display_interval
	 *
	 * @access public
	 * @throws Exception
	 */
	public function display_interval() {
		if (!isset($_GET['start']) || !isset($_GET['end'])) {
			echo json_encode([ "error" => "Values 'start' and 'end' not provided." ]);
			exit;
		}

		$datetime_start = \DateTime::createFromFormat("Y-m-d", $_GET['start'])->setTime(0, 0, 0);
		$datetime_end = \DateTime::createFromFormat("Y-m-d", $_GET['end'])->setTime(23, 59, 59);

		if ($datetime_start === false || $datetime_end === false) {
			echo json_encode([ "error" => "Invalid date format. Please use 'YYYY-MM-DD'." ]);
			exit;
		}

		$bank_account = \Bank_Account::get_by_id($_GET['id']);
		$statements = \Bank_Account_Statement::get_by_bank_account_with_dynamic_resolution($bank_account, 1000);

		$data = [];
		foreach ($statements as $statement) {
			$datetime_statement = \DateTime::createFromFormat("Y-m-d", $statement->original_situation_date);

			if ($datetime_statement < $datetime_start || $datetime_statement > $datetime_end) {
				continue;
			}

			$data[] = [
				'x' => floatval(strtotime($statement->original_situation_date) . '000') + 86400000,
				'y' => floatval($statement->original_situation_balance),
				'id' => $statement->sequence
			];
		}

		echo json_encode($data, JSON_PRETTY_PRINT);
	}
}
