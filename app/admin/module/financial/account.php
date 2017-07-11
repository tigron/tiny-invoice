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
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Financial_Account extends Module {

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

		$bank_accounts = Bank_Account::get_all();
		$template->assign('bank_accounts', $bank_accounts);
	}

	/**
	 * Import
	 *
	 * @access public
	 */
	public function display_import() {
		if (isset($_POST['import'])) {
			$file = File::get_by_id($_POST['import']['file_id']);
			$parsers = Bank_Account_Statement_Parser::get_all();
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

		$parsers = Bank_Account_Statement_Parser::get_all();

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

	/**
	 * Edit an account
	 *
	 * @access public
	 */
	public function display_edit() {
		Session::redirect('/financial/account/transaction?id=' . $_GET['id']);
	}


}
