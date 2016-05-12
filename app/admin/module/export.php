<?php
/**
 * Module Export
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use Skeleton\Package\Crud\Web\Module\Crud;
use Skeleton\Pager\Web\Pager;
use Skeleton\Core\Web\Session;

use Yasumi\Yasumi;
use Yasumi\Filters\OfficialHolidaysFilter;

class Web_Module_Export extends Crud {

    /**
     * The template
     *
     * @access public
     */
    public $template = 'export.twig';

    /**
     * Get the pager object
     *
     * @access public
     * @return Pager $pager
     */
    public function get_pager() {
// Use the factory to create a new holiday provider instance
$holidays = Yasumi::create('Belgium', 2016);

foreach ($holidays->getHolidayDates() as $date) {
//    echo $date . PHP_EOL;
}

    	$pager = new Pager('export');
    	$pager->add_sort_permission('created');
    	$pager->add_sort_permission('id');
    	$pager->add_sort_permission('file.name');
    	$pager->set_sort('id');
    	$pager->set_direction('desc');
    	$pager->page();
    	return $pager;
    }

	/**
	 * Export scheduled
	 *
	 * @access public
	 */
    public function display_created() {
    	Session::set_sticky('message', 'updated');
    	Session::redirect('/export');
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
	 * Is editable
	 *
	 * @access public
	 * @param Object $object
	 */
    public function is_editable($object) {
    	return false;
    }

	/**
	 * Is deletable
	 *
	 * @access public
	 * @param Object $object
	 */
    public function is_deletable($object) {
    	return true;
    }
}
