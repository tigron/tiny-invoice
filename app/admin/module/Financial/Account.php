<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Financial;

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Application\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

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
	protected $template = 'financial/account.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();

		$bank_accounts = \Bank_Account::get_all();
		$template->assign('bank_accounts', $bank_accounts);

		$bookkeeping_accounts = \Bookkeeping_Account::get_all();
		$template->assign('bookkeeping_accounts', $bookkeeping_accounts);
	}

	/**
	 * Edit the bank account
	 *
	 * @access public
	 */
	public function display_edit() {
		if (isset($_POST['bank_account']['default_for_payment'])) {
			$_POST['bank_account']['default_for_payment'] = true;
			$bank_accounts = \Bank_Account::get_all();
			foreach ($bank_accounts as $bank_account) {
				$bank_account->default_for_payment = false;
				$bank_account->save(false);
			}
		} else {
			$_POST['bank_account']['default_for_payment'] = false;
		}

		$bank_account = \Bank_Account::get_by_id($_GET['id']);
		$bank_account->load_array($_POST['bank_account']);
		$bank_account->save();

		Session::redirect('/financial/account');
	}

	/**
	 * Edit the bank account
	 *
	 * @access public
	 */
	public function display_add() {
		if (isset($_POST['bank_account']['default_for_payment'])) {
			$_POST['bank_account']['default_for_payment'] = true;
			$bank_accounts = \Bank_Account::get_all();
			foreach ($bank_accounts as $bank_account) {
				$bank_account->default_for_payment = false;
				$bank_account->save(false);
			}
		} else {
			$_POST['bank_account']['default_for_payment'] = false;
		}

		$bank_account = new \Bank_Account();
		$bank_account->load_array($_POST['bank_account']);
		$bank_account->save();

		Session::redirect('/financial/account');
	}

	/**
	 * Export a bank account
	 *
	 * @access public
	 */
	public function display_export() {
		$template = Template::get();

		$bank_account = \Bank_Account::get_by_id($_GET['id']);
		$template->assign('bank_account', $bank_account);

		if (isset($_POST['export_format'])) {
			$export = new $_POST['export_format']();
			$export->data = json_encode( $_POST['bank_account_statement_ids'] );
			$export->save();
			$export->run();
			Session::redirect('/export?action=created');
		}
	}

	/**
	 * Import
	 *
	 * @access public
	 */
	public function display_import() {
		if (isset($_POST['import'])) {
			$file = \File::get_by_id($_POST['import']['file_id']);
			$parsers = \Bank_Account_Statement_Parser::get_all();
			$valid_parser = null;
			foreach ($parsers as $parser) {
				if ($parser->detect($file)) {
					$valid_parser = $parser;
				}
			}

			if ($valid_parser === null) {
				throw new \Exception('Cannot import this');
			}

			$valid_parser->parse($file);
			Session::redirect('/financial/account?action=import_finish');
		}
	}

	/**
	 * Import finished
	 *
	 * @access public
	 */
	public function display_import_finish() {
		$template = Template::get();
	}

	/**
	 * Validate a bank account
	 *
	 * @access public
	 */
	public function display_validate() {
		if (isset($_POST['bank_account']['id'])) {
			$bank_account = \Bank_Account::get_by_id($_POST['bank_account']['id']);
		} else {
			$bank_account = new \Bank_Account();
		}

		$bank_account->load_array($_POST['bank_account']);
		$bank_account->validate($errors);
		echo json_encode($errors);
		$this->template = false;
	}

	/**
	 * Add file (ajax)
	 *
	 * @access public
	 */
	public function display_add_file() {
		$this->template = false;

		if (!isset($_FILES['file'])) {
			echo json_encode(['error' => true]);
			return;
		}

		$file = \Skeleton\File\File::upload($_FILES['file']);
		$file->expire();

		$parsers = \Bank_Account_Statement_Parser::get_all();

		$valid_parser = null;
		foreach ($parsers as $parser) {
			if ($parser->detect($file)) {
				$valid_parser = $parser;
			}
		}

		if ($valid_parser !== null) {
			echo json_encode(
				[
					'file' => $file->get_info(true),
					'parser' => $valid_parser->get_info(),
				]
			);
		} else {
			echo json_encode(
				[
					'file' => $file->get_info(true),
					'parser' => false,
				]
			);
		}
	}
}
