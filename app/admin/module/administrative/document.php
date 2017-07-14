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

		if (isset($_POST['type']) AND !empty($_POST['type'])) {
			$pager->add_condition('classname', $_POST['type']);
		}

		$pager->add_sort_permission('id');
		$pager->add_sort_permission('document.date');
		$pager->add_sort_permission('title');
		$pager->add_sort_permission('file.name');

		$pager->set_sort('document.date');
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
	 * Add a document
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Template::get();

		if (isset($_POST['document'])) {
			$selected_tags = [];
			if (!empty($_POST['tag_ids'])) {
				$tag_ids = explode(',', $_POST['tag_ids']);
				foreach ($tag_ids as $tag_id) {
					$selected_tags[] = Tag::get_by_id($tag_id);
				}
			}

			$document = new Document();
			$document->classname = 'Document';
			$document->load_array($_POST['document']);
			if ($document->validate($errors) === false) {
				$template->assign('errors', $errors);
				$template->assign('document', $document);
				$template->assign('selected_tags', $selected_tags);
			} else {
				$document->save();
				$document->date = $document->created;
				$document->save();

				foreach ($selected_tags as $tag) {
					$document->add_tag($tag);
				}

				Session::set_sticky('message', 'created');
				Session::redirect($this->get_module_path() . '?action=edit&id=' . $document->id);
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
				try {
					$document->file->expire();
					$document->file_id = NULL;
				} catch (Exception $e) { }
			}
			if (isset($_POST['tag_ids'])) {
				$selected_tags = [];
				$tag_ids = array_filter(explode(',', $_POST['tag_ids']));
				foreach ($tag_ids as $tag_id) {
					$selected_tags[] = Tag::get_by_id($tag_id);
				}
			}

			/**
			 * Incoming invoices
			 */
			if (isset($_POST['payment_message_type'])) {
				if ($_POST['payment_message_type'] == 'payment_message_type_structured') {
					$_POST['document']['payment_message'] = '';
				} else {
					$_POST['document']['payment_structured_message'] = '';
				}
			}

			if (empty($_POST['document']['paid'])) {
				$_POST['document']['paid'] = false;
			}  else {
				$_POST['document']['paid'] = true;
			}

			/**
			 * Contract
			 */
			if (isset($_POST['contract_for']) and $_POST['document']['classname'] == 'Document_Contract') {

				if ($_POST['contract_for'] == 'supplier') {
					$_POST['document']['customer_id'] = 0;
				} else {
					$_POST['document']['supplier_id'] = 0;
				}
			}

			/**
			 * Documentation
			 */
			if (isset($_POST['documentation_for']) and $_POST['document']['classname'] == 'Document_Documentation') {

				if ($_POST['documentation_for'] == 'supplier') {
					$_POST['document']['customer_id'] = 0;
				} else {
					$_POST['document']['supplier_id'] = 0;
				}
			}

			$document = $document->change_classname($_POST['document']['classname']);
			unset($_POST['document']['classname']);
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
				Session::redirect($this->get_module_path() . '?action=edit&id=' . $document->id);
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
	 * Ajax extractor
	 *
	 * @access public
	 */
	public function display_ajax_extractor() {
		$this->template = false;
		$document = Document::get_by_id($_GET['id']);
		$extractors = Extractor_Pdf::get_all();
		foreach ($extractors as $extractor) {
			if ($extractor->match($document)) {
				echo json_encode($extractor->get_info());
				return;
			}
		}
		echo json_encode(false);
	}

	/**
	 * Ajax extract content
	 *
	 * @access public
	 */
	public function display_ajax_extract_content() {
		$this->template = 'administrative/document/extractor/extract_content.twig';
		$document = Document::get_by_id($_GET['id']);
		$extractor = Extractor_Pdf::get_by_id($_GET['extractor_id']);

		try {
			$extract = $extractor->extract_data($document);
			$fields = $extract['data'];
		} catch (Eval_Exception $e) {
			$fields = [];
		}

		$template = Template::get();
		$template->assign('fields', $fields);
		$template->assign('document', $document);
		$template->assign('extractor', $extractor);
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
	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.document';
	}
}
