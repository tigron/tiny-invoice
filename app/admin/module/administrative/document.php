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
use \Skeleton\File\Store;

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

		$template = Template::Get();
		$template->assign('pager', $pager);
		$template->assign('tags', Tag::get_all());
	}

	/**
	 * Add a document
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Template::Get();

		if (isset($_POST['document'])) {
			$document = new Document();
			$document->load_array($_POST['document']);
			if ($document->validate($errors) === false) {
				$template->assign('errors', $errors);
				$template->assign('document', $document);
			} else {
				$document->save();
				Session::set_sticky('message', 'created');
				Session::Redirect('/administrative/document?action=edit&id=' . $document->id);
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

		$file = Store::upload($_FILES['file']);
		$file->expire();

		echo json_encode(['file' => $file->get_info()]);
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function display_delete() {
		$document = Document::get_by_id($_GET['id']);
		$document->delete();
		Session::Redirect('/administrative/document');
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Template::Get();

		$document = Document::get_by_id($_GET['id']);
		$template->assign('document', $document);

		if (isset($_POST['document'])) {
			if (isset($_POST['document']['created'])) {
				if (strpos($_POST['document']['created'], '/') !== false) {
					list($day, $month, $year) = explode('/', $_POST['document']['created']);
				} else {
					list($year, $month, $day) = explode('-', $_POST['document']['created']);
				}
				$_POST['document']['created'] = $year . '-' . $month . '-' . $day;
			}
			$document->load_array($_POST['document']);
			$document->save();
			$session->message = 'document_updated';
			Session::Redirect('/administrative/document?action=edit&id=' . $document->id);
		}

		$tag_ids = array();
		foreach ($document->get_document_tags() as $document_tag) {
			$tag_ids[] = $document_tag->tag_id;
		}
		$template->assign('tag_ids', $tag_ids);

		$tags = Tag::get_all();
		$template->assign('tags', $tags);
	}

	/**
	 * Replace a document
	 *
	 * @access public
	 */
	public function display_replace_document() {
		$document = Document::get_by_id($_POST['id']);

		if (isset($_FILES['document']) AND $_FILES['document']['error'] == 0) {
			$file = File_Store::upload($_FILES['document']);

			$document->file->delete();

			$document->file_id = $file->id;
			$document->save();
		}
		Session::Redirect('/administrative/document?action=edit&id=' . $document->id);
	}

	/**
	 * Edit tags
	 *
	 * @access public
	 */
	public function display_edit_tags() {
		$document = Document::get_by_id($_GET['id']);
		$document_tags = $document->get_document_tags();
		foreach ($document_tags as $document_tag) {
			$document_tag->delete();
		}

		foreach ($_POST['tag'] as $tag_id => $selected) {
			$document_tag = new Document_Tag();
			$document_tag->document_id = $document->id;
			$document_tag->tag_id = $tag_id;
			$document_tag->save();
		}

		Session::set_sticky('message', 'tags_updated');
		Session::Redirect('/administrative/document?action=edit&id=' . $document->id);
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
}
