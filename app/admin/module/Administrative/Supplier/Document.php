<?php
/**
 * Web Module Administrative Customer
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Administrative\Supplier;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;
use \Skeleton\Pager\Web\Pager;

class Document extends Module {
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
	public $template = 'administrative/supplier/document.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();
		$supplier = \Supplier::get_by_id($_GET['id']);
		$template->assign('supplier', $supplier);

		$contracts = \Document_Contract::get_by_supplier($supplier);
		$documentations = \Document_Documentation::get_by_supplier($supplier);

		$sorted = [];
		foreach ($contracts as $contract) {
			$sorted[$contract->id] = $contract;
		}

		foreach ($documentations as $documentation) {
			$sorted[$documentation->id] = $documentation;
		}
		$template->assign('documents', $sorted);
	}
}
