<?php
/**
 * Module Index
 *
 * @author Hassan Ahmed <hassan.ahmed@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */


namespace App\Admin\Module\Administrative\Document;

use \Skeleton\Application\Web\Template;
use \Skeleton\Core\Http\Session;
use \Skeleton\Pager\Web\Pager;

class Documentation extends \App\Admin\Module\Administrative\Document {

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
					$tag = \Tag::get_by_id($tag_id);
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
		$pager->add_condition('classname', 'Document_Documentation');
		$pager->add_join('document_documentation', 'document_id', 'document.id');

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('document.date');
		$pager->add_sort_permission('title');

		$pager->set_sort('document.date');
		$pager->set_direction('DESC');
		if (isset($_GET['search'])) {
			$pager->set_condition('%search%', $_GET['search']);
		}
		if (isset($_GET['tags'])) {
			$pager->set_condition('tag_id', $_GET['tags']);
		}
		$pager->page();

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/administrative/document/documentation');
		}

		$template = Template::get();
		$conditions = $pager->get_conditions();
		if (isset($conditions['document_tag.tag_id'])) {
			$tag_ids = $conditions['document_tag.tag_id'][0]->get_value();
			$selected_tags = [];
			foreach ($tag_ids as $tag_id) {
				$selected_tags[] = \Tag::get_by_id($tag_id);
			}
			$template->assign('selected_tags', $selected_tags);
		}

		$template->assign('pager', $pager);
		$template->assign('tags', \Tag::get_all());
		$template->assign('selected_tags', $selected_tags);
	}

	/**
	 * Edit a Documentation
	 *
	 * @access public
	 */
	public function display_edit() {
		$document = \Document::get_by_id($_GET['id']);
		if ($document->classname != 'Document_Documentation') {
			Session::redirect('/administrative/document?action=edit&id=' . $_GET['id']);
		}

		parent::display_edit();
	}

	/**
	 * AJAX: check supplier_identifier
	 *
	 * @access public
	 */
	public function display_check_supplier_identifier() {
		$this->template = null;

		try {
			$supplier = \Supplier::get_by_id($_POST['supplier_id']);
			$documentations = \Document_Documentation::get_by_supplier_supplier_identifier($supplier, $_POST['supplier_identifier']);

			foreach ($documentations as $key => $documentation) {
				if ($documentation->id == $_POST['document_id']) {
					unset($documentation[$key]);
				}
			}

			echo count($documentations);
		} catch (\Exception $e) {
			echo 0;
		}
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
