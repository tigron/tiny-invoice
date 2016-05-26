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

class Web_Module_Setting_Extractor extends Crud {

    /**
     * The template
     *
     * @access public
     */
    public $template = 'setting/extractor.twig';

    /**
     * Get the pager object
     *
     * @access public
     * @return Pager $pager
     */
    public function get_pager() {
    	$pager = new Pager('extractor');
    	$pager->add_sort_permission('created');
    	$pager->add_sort_permission('id');
    	$pager->add_sort_permission('extractor.name');
    	$pager->add_sort_permission('file.name');
    	$pager->set_sort('id');
    	$pager->set_direction('desc');
    	$pager->page();
    	return $pager;
    }

	/**
	 * Eval code
	 *
	 * @access public
	 */
    public function display_eval() {
    	$extractor = Extractor::get_by_id($_GET['id']);
		$extractor->eval = $_POST['eval'];
		$extractor->save();
		$this->template = false;
		print_r($extractor->parse_content());
    }

    /**
     * Create
     *
     * @access public
     */
    public function display_create() {
    	$document = Document::get_by_id($_GET['document_id']);
    	$extractor = new Extractor();
    	$extractor->name = 'extractor for ' . $document->file->name;
    	$extractor->document_id = $document->id;
    	$extractor->save();
    	Session::redirect('/setting/extractor?action=edit&id=' . $extractor->id);
    }

    /**
     * Is creatable
     *
     * @access public
     */
    public function is_creatable() {
    	return false;
    }

}
