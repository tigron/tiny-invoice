<?php
/**
 * Module Setting Company
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_Setting_Company extends Web_Module {
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
	public $template = 'setting/company.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$session = Web_Session_Sticky::Get();
		$template = Web_Template::Get();

		if (isset($_POST['setting'])) {
			foreach ($_POST['setting'] as $key => $value) {
				try {
					$setting = Setting::get_by_name($key);
				} catch (Exception $e) {
					$setting = new Setting();
					$setting->name = $key;
				}
				$setting->value = $value;
				$setting->save();
			}
			$session->message = 'updated';
			Web_Session::Redirect('/setting/company');
		}

		$template->assign('settings', Setting::get_as_array());
		$template->assign('countries', Country::get_grouped());
	}
}
