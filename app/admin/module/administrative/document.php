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

class Web_Module_Administrative_Document extends Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = true;

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = 'administrative/document.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$pager = new Pager('document');

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('created');
		$pager->add_sort_permission('title');
		$pager->add_sort_permission('file.name');

		$pager->set_sort('created');
		$pager->set_direction('DESC');
		if (isset($_GET['search'])) {
			$pager->set_condition('%search%', $_GET['search']);
		}
		if (isset($_GET['tags'])) {
			$pager->set_condition('tag_id', $_GET['tags']);
		}
		$pager->set_sort('created');
		$pager->set_direction('DESC');
		$pager->page();

		$template = Template::get();
		$template->assign('pager', $pager);
		$template->assign('tags', Tag::get_all());
	}

	/**
	 * Add a document
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Template::get();

		if (isset($_POST['document'])) {
			$selected_tags = [];
			if (isset($_POST['tag_ids'])) {
				$tag_ids = explode(',', $_POST['tag_ids']);
				foreach ($tag_ids as $tag_id) {
					$selected_tags[] = Tag::get_by_id($tag_id);
				}
			}

			$document = new Document();
			$document->load_array($_POST['document']);
			if ($document->validate($errors) === false) {
				$template->assign('errors', $errors);
				$template->assign('document', $document);
				$template->assign('selected_tags', $selected_tags);
			} else {
				$document->save();

				foreach ($selected_tags as $tag) {
					$document->add_tag($tag);
				}

				Session::set_sticky('message', 'created');
				Session::redirect('/administrative/document?action=edit&id=' . $document->id);
			}
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

		echo json_encode(['file' => $file->get_info(true)]);
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::get();
		$document = Document::get_by_id($_GET['id']);
		$selected_tags = $document->get_tags();

		if (isset($_POST['document'])) {

			if (!isset($_POST['document']['file_id']) OR $document->file_id != $_POST['document']['file_id']) {
				$document->file->expire();
				$document->file_id = NULL;
			}

			$selected_tags = [];
			if (isset($_POST['tag_ids'])) {
				$tag_ids = array_filter(explode(',', $_POST['tag_ids']));
				foreach ($tag_ids as $tag_id) {
					$selected_tags[] = Tag::get_by_id($tag_id);
				}
			}

			$document->load_array($_POST['document']);
			if ($document->validate($errors) === false) {
				$template->assign('errors', $errors);
			} else {
				$document->save();
				$document->file->cancel_expiration();

				$document->remove_tags();
				foreach ($selected_tags as $tag) {
					$document->add_tag($tag);
				}

				Session::set_sticky('message', 'updated');
				Session::redirect('/administrative/document?action=edit&id=' . $document->id);
			}
		}

		$template->assign('document', $document);
		$template->assign('selected_tags', $selected_tags);
		$template->assign('tags', Tag::get_all());
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function display_delete() {
		$document = Document::get_by_id($_GET['id']);
		$document->delete();

		Session::set_sticky('message', 'deleted');
		Session::redirect('/administrative/document');
	}

	/**
	 * Download
	 *
	 * @access public
	 */
	public function display_download() {
		$document = Document::get_by_id($_GET['id']);
		$document->file->client_download();
	}

	/**
	 * Search documents (ajax)
	 *
	 * @access public
	 */
	public function display_ajax_search() {
		$this->template = '/administrative/document/list.twig';

		$pager = new Pager('document');
		$pager->add_sort_permission('title');
		$pager->add_sort_permission('created');

		if (isset($_GET['search'])) {
			$pager->set_search($_GET['search']);
		}

		if (isset($_GET['tags']) AND $_GET['tags'] != '') {
			$pager->add_join('document_tag', 'document_id', 'document.id');
			$pager->add_condition('document_tag.tag_id', explode(',', $_GET['tags']));
		}

		$pager->page(false);

		$template = Template::get();
		$template->assign('pager', $pager);
	}

	/**
	 * Load document (ajax)
	 *
	 * @access public
	 */
	public function display_load() {
		$this->template = null;
		$document = Document::get_by_id($_GET['id']);
		echo json_encode($document->get_info());
	}

}
