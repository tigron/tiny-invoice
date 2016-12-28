<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

require_once dirname(__FILE__) . '/../document.php';

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Document_Documentation extends Web_Module_Administrative_Document {

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = 'administrative/document/documentation.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$pager = new Pager('document');

		$selected_tags = [];
		$tag_ids = [];
		if (!empty($_POST['tag_ids'])) {
			$tag_ids = explode(',', $_POST['tag_ids']);
			if (count($tag_ids) > 0) {
				foreach ($tag_ids as $tag_id) {
					$tag = Tag::get_by_id($tag_id);
					$selected_tags[] = $tag;
				}
			}
		}

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}

		if (count($tag_ids) > 0) {
			$pager->add_join('document_tag', 'document_id', 'document.id');
			$pager->add_condition('document_tag.tag_id', 'IN', $tag_ids);
		}

		// Fix for pager
		$pager->add_condition('document_documentation.document_id', '>', 0);
//		$pager->add_condition('classname', 'Document_Documentation');
		$pager->add_join('document_documentation', 'document_id', 'document.id');

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('date');
		$pager->add_sort_permission('title');

		$pager->set_sort('date');
		$pager->set_direction('DESC');
		if (isset($_GET['search'])) {
			$pager->set_condition('%search%', $_GET['search']);
		}
		if (isset($_GET['tags'])) {
			$pager->set_condition('tag_id', $_GET['tags']);
		}
		$pager->page();

		$template = Template::get();
		$template->assign('pager', $pager);
		$template->assign('tags', Tag::get_all());
		$template->assign('selected_tags', $selected_tags);
	}

	/**
	 * Edit an incoming invoice
	 *
	 * @access public
	 */
	public function display_edit() {
		$document = Document::get_by_id($_GET['id']);
		if ($document->classname != 'Document_Incoming_Invoice') {
			Session::redirect('/administrative/document?action=edit&id=' . $_GET['id']);
		}

		parent::display_edit();
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.document';
	}

}
