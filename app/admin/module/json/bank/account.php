<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;

class Web_Module_Json_Bank_Account extends Module {

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
		$bank_account = Bank_Account::get_by_id($_GET['id']);

		$statements = Bank_Account_Statement::get_by_bank_account($bank_account);
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

}
