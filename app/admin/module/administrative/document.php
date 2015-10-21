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
<<<<<<< HEAD
=======
use \Skeleton\File\Store;
>>>>>>> origin/master

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
			$document = new Document();
			$document->load_array($_POST['document']);
			if ($document->validate($errors) === false) {
				$template->assign('errors', $errors);
				$template->assign('document', $document);
			} else {
				$document->save();
<<<<<<< HEAD

				Session::set_sticky('message', 'created');
				Session::redirect('/administrative/document?action=edit&id=' . $document->id);
=======
				Session::set_sticky('message', 'created');
				Session::Redirect('/administrative/document?action=edit&id=' . $document->id);
>>>>>>> origin/master
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

<<<<<<< HEAD
		$file = File::upload($_FILES['file']);
=======
		$file = Store::upload($_FILES['file']);
>>>>>>> origin/master
		$file->expire();

		echo json_encode(['file' => $file->get_info(true)]);
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
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
<<<<<<< HEAD
		$template = Template::get();
=======
		$template = Template::Get();
>>>>>>> origin/master

		$document = Document::get_by_id($_GET['id']);
		$template->assign('document', $document);

		if (isset($_POST['document'])) {
			$document->load_array($_POST['document']);
			$document->save();
<<<<<<< HEAD
			Session::set_sticky('message', 'document_updated');
			Session::redirect('/administrative/document?action=edit&id=' . $document->id);
=======

			Session::set_sticky('message', 'document_updated');
			Session::Redirect('/administrative/document?action=edit&id=' . $document->id);
>>>>>>> origin/master
		}

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
			$file = File::upload($_FILES['document']);

			$document->file->delete();

			$document->file_id = $file->id;
			$document->save();
		}

		Session::redirect('/administrative/document?action=edit&id=' . $document->id);
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

		if (isset($_POST['tag'])) {
			foreach ($_POST['tag'] as $tag_id) {
				$tag = Tag::get_by_id($tag_id);
				$document->add_tag($tag);
			}
		}

<<<<<<< HEAD
		Session::set_sticky('message', 'tag_updated');
		Session::redirect('/administrative/document?action=edit&id=' . $document->id);
=======
		Session::set_sticky('message', 'tags_updated');
		Session::Redirect('/administrative/document?action=edit&id=' . $document->id);
>>>>>>> origin/master
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
