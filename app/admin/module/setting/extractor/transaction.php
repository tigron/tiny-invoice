<?php
/**
 * Web Module Transaction Extractor
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Setting_Extractor_Transaction extends Module {
	/**
	 * Login required
	 *
	 * @access protected
	 * @var bool $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'setting/extractor/transaction.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();

		$pager = new Pager('extractor_bank_account_statement_transaction');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		$template = Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Add
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Template::Get();

		if (isset($_GET['transaction_id'])) {
			$transaction = Bank_Account_Statement_Transaction::get_by_id($_GET['transaction_id']);
			$template->assign('transaction', $transaction);
		}

		if (isset($_POST['extractor']['bank_account_statement_transaction_id'])) {
			$transaction = Bank_Account_Statement_Transaction::get_by_id($_POST['extractor']['bank_account_statement_transaction_id']);
			$template->assign('transaction', $transaction);
		}


		if (isset($_POST['extractor'])) {
			$extractor = new Extractor_Bank_Account_Statement_Transaction();
			$extractor->load_array($_POST['extractor']);
			if ($extractor->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$extractor->save();

				Session::set_sticky('message', 'created');
				Session::redirect('/setting/extractor/transaction?action=edit&id=' . $extractor->id);
			}
		}
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::Get();
		$extractor = Extractor_Bank_Account_Statement_Transaction::get_by_id($_GET['id']);
		$template->assign('extractor', $extractor);

		if (isset($_POST['extractor'])) {
			$extractor->load_array($_POST['extractor']);
			if ($extractor->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$extractor->save();

				Session::set_sticky('message', 'updated');
				Session::redirect('/setting/extractor/transaction?action=edit&id=' . $extractor->id);
			}
		}
	}

	/**
	 * Eval code
	 *
	 * @access public
	 */
    public function display_eval() {
    	$extractor = Extractor_Bank_Account_Statement_Transaction::get_by_id($_GET['id']);
		$extractor->eval = $_POST['eval'];
		$extractor->save();
		$this->template = false;
		$reponse = [];
		try {
			$extract = $extractor->extract_data();
			$response['message'] = print_r($extract['output'], true);
			$data = $extract['data'];
			$response['data'] = [];
			foreach ($data as $row) {
				$entry = [];
				$entry['classname'] = get_class($row['link_to']);
				$entry['id'] = $row['link_to']->id;
				$entry['amount'] =$row['amount'];
				$response['data'][] = $entry;
			}
			$response['error'] = false;
		} catch (Extractor_Eval_Exception $e) {
			$response['message'] = $e->getMessage() . ' on line ' . $e->line;
			$response['error'] = true;
		}

		echo json_encode($response);
    }

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function display_delete() {
		$extractor = Extractor_Bank_Account_Statement_Transaction::get_by_id($_GET['id']);
		$extractor->delete();
		Session::set_sticky('message', 'deleted');
		Session::redirect('/setting/extractor/transaction');
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.setting';
	}
}
