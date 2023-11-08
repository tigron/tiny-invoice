<?php
/**
 * Module Setting Company
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Setting;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;

class Company extends Module {
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
		$template = Template::Get();

		if (isset($_POST['setting'])) {
			foreach ($_POST['setting'] as $key => $value) {
				try {
					$setting = \Setting::get_by_name($key);
				} catch (\Exception $e) {
					$setting = new \Setting();
					$setting->name = $key;
				}
				$setting->value = $value;
				$setting->save();
			}
			Session::set_sticky('message', 'updated');
			Session::redirect('/setting/company');
		}

		$template->assign('settings', \Setting::get_as_array());
		$template->assign('countries', \Country::get_grouped());
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
