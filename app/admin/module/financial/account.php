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

		$document = Document::get_by_id(59);


		// create a document instance
		$document = SetaPDF_Core_Document::loadByFilename($document->file->get_path());

		// create an extractor instance
		$extractor = new SetaPDF_Extractor($document);

		// get the plain text from page 1
		$result = $extractor->getResultByPageNumber(1);

		// output
		echo '<pre>';
		echo $result;
		echo '</pre>';

die();

		$template = Template::Get();

		$pager = new Pager('bank_account');

		$pager->add_sort_permission('identifier');
		$pager->add_sort_permission('name');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}
		$pager->page();

		$template = Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Import
	 *
	 * @access public
	 */
	public function display_import() {
		$parsers = Bank_Account_Statement_Parser::get_all();
		$file = \Skeleton\File\File::get_by_id(183);
		foreach ($parsers as $parser) {

			print_r($parser->detect($file));
		}

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


}
