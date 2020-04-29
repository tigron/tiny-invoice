<?php
/**
 * Web Module Administrative Customer
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Supplier_Detail extends Module {
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
	public $template = 'administrative/supplier/detail.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();
		$supplier = Supplier::get_by_id($_GET['id']);

		if (isset($_POST['supplier'])) {
			$_POST['supplier']['iban'] = str_replace(' ', '', $_POST['supplier']['iban']);
			$supplier->load_array($_POST['supplier']);
			$supplier->validate($errors);

			if (isset($_POST['ignore_vat'])) {
				unset($errors['vat']);
			}

			if (count($errors) > 0) {
				$template->assign('errors', $errors);
			} else {
				$supplier->save(false);

				Session::set_sticky('message', 'updated');
				Session::redirect('/administrative/supplier/detail?action=edit&id=' . $supplier->id);
			}
		}

		$template->assign('supplier', $supplier);
		$template->assign('languages', Language::get_all());
		$template->assign('countries', Country::get_grouped());
	}

}
