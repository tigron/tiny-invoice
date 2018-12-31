<?php
/**
 * Module Extractor
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use Skeleton\Package\Crud\Web\Module\Crud;
use Skeleton\Pager\Web\Pager;
use Skeleton\Core\Web\Session;
use Skeleton\Core\Web\Template;

class Web_Module_Setting_Extractor_Pdf extends Crud {

    /**
     * The template
     *
     * @access public
     */
    public $template = 'setting/extractor/pdf.twig';

    /**
     * Display
     *
     * @access public
     */
    public function display() {
    	$this->check_setasign();
    	parent::display();
    }

    /**
     * Get the pager object
     *
     * @access public
     * @return Pager $pager
     */
    public function get_pager() {
    	$pager = new Pager('extractor_pdf');
    	$pager->add_sort_permission('created');
    	$pager->add_sort_permission('id');
    	$pager->add_sort_permission('extractor_pdf.name');
    	$pager->add_sort_permission('file.name');
    	$pager->add_sort_permission('last_used');
    	$pager->set_sort('id');
    	$pager->set_direction('desc');
    	$pager->page();
    	return $pager;
    }

    /**
     * Check setasign
     *
     * @access private
     */
    private function check_setasign() {
		/**
		 * Set the root path
		 */
		$root_path = realpath(dirname(__FILE__) . '/../../../../../');

		/**
		 * Check if Seta is loaded
		 */
		try {
			$setting = Setting::get_by_name('setasign_pdf_extractor');
		} catch (Exception $e) {
			$setting = new Setting();
			$setting->name = 'setasign_pdf_extractor';
			$setting->value = 0;
		}
		$new_value = $setting->value;

		if (file_exists($root_path . '/lib/component/SetaPDF/Autoload.php')) {
			require_once $root_path . '/lib/component/SetaPDF/Autoload.php';
			$new_value = 1;
		} else {
			$new_value = 0;
		}

		$template = Template::get();

		if ($new_value != $setting->value and $new_value == 1) {
			$template->assign('action', 'setasign_enabled');
		}

		$setting->value = $new_value;
		$setting->save();

		try {
			$setting->save();
		} catch (Exception $e) { }

		if ($setting->value == 0) {
			$template->assign('action', 'setasign_disabled');
		}
    }

	/**
	 * Eval code
	 *
	 * @access public
	 */
    public function display_eval() {
    	$extractor = Extractor_Pdf::get_by_id($_GET['id']);
		$extractor->eval = $_POST['eval'];
		$extractor->save();
		$this->template = false;
		$reponse = [];
		try {
			$extract = $extractor->extract_data();
			$response['message'] = print_r($extract['output'], true);
			$response['data'] = $extract['data'];
			$response['error'] = false;
		} catch (Extractor_Eval_Exception $e) {
			$response['message'] = $e->getMessage() . ' on line ' . $e->line;
			$response['error'] = true;
		}

		echo json_encode($response);
    }

    /**
     * Create
     *
     * @access public
     */
    public function display_create() {
    	$document = Document::get_by_id($_GET['document_id']);
    	$extractor = new Extractor_Pdf();
    	$extractor->name = 'extractor for ' . $document->file->name;
    	$extractor->document_id = $document->id;
    	$extractor->save();
    	Session::redirect('/setting/extractor/pdf?action=edit&id=' . $extractor->id);
    }

    /**
     * Search document (AJAX)
     *
     * @access public
     */
    public function display_search_document() {
		$this->template = false;
    	$query = $_GET['query'];

    	$pager = new Pager('document');
    	$pager->set_search($query);
    	$pager->set_sort('created');
    	$pager->add_sort_permission('created');
    	$pager->set_direction('desc');
    	$pager->page();

		$results = [];
    	foreach ($pager->items as $item) {
    		$result = $item->id . ' - ' . $item->title;
    		if (isset($item->description) && $item->description != '') {
    			$result .= ' (' . $item->description . ')';
    		}
    		$results[] = [ 'id' => $item->id, 'title' => $item->title, 'description' => $item->description, 'result' => $result ];
    	}
    	echo json_encode($results);
    }

    /**
     * Change document
     *
     * @access public
     */
    public function display_change_document() {
    	$extractor_pdf = Extractor_Pdf::get_by_id($_GET['id']);
    	$extractor_pdf->document_id = $_POST['document_id'];
    	$extractor_pdf->save();
    	Session::redirect('/setting/extractor/pdf?action=edit&id=' . $_GET['id']);
    }

    /**
     * Is creatable
     *
     * @access public
     */
    public function is_creatable() {
    	return false;
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
