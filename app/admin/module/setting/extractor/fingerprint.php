<?php
/**
 * Module Extractor
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use Skeleton\Pager\Web\Pager;
use Skeleton\Core\Web\Session;
use Skeleton\Core\Web\Module;
use Skeleton\Core\Web\Template;

class Web_Module_Setting_Extractor_Fingerprint extends Module {

    /**
     * The template
     *
     * @access public
     */
    public $template = 'setting/extractor/fingerprint.twig';


    public function display() {
		$extractor = Extractor::get_by_id($_GET['id']);

		$template = Template::get();
		$template->assign('extractor', $extractor);
    }

    public function display_add_fingerprint() {
		$coordinates = json_decode($_POST['coordinates'], true)[0];
		$sort = $coordinates['id'];
		unset($coordinates['id']);
		$fingerprint = new Extractor_Fingerprint();
		$fingerprint->extractor_id = $_GET['id'];
		$fingerprint->load_array($coordinates);
		$fingerprint->sort = $sort;
		$fingerprint->save();

		$this->template = 'setting/extractor/ajax_fingerprint.twig';
		$template = Template::get();
		$template->assign('fingerprint', $fingerprint);
    }

    /**
     * Clear fingerprints
     *
     * @access public
     */
    public function display_clear_fingerprints() {
    	$extractor = Extractor::get_by_id($_GET['id']);
    	$fingerprints = $extractor->get_extractor_fingerprints();
    	foreach ($fingerprints as $fingerprint) {
    		$fingerprint->delete();
    	}
    	$this->template = false;
    }
}
