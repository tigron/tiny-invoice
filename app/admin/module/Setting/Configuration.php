<?php

/**
 * Module Setting Configuration
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Setting;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;

class Configuration extends Module {
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
	public $template = 'setting/configuration.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();

		if (isset($_POST['setting'])) {
			if (!isset($_POST['setting']['enable_click_post'])) {
				$_POST['setting']['enable_click_post'] = false;
			}
			if (!isset($_POST['setting']['enable_invoice_reminder'])) {
				$_POST['setting']['enable_invoice_reminder'] = false;
			}
			if (!isset($_POST['setting']['enable_codabox'])) {
				$_POST['setting']['enable_codabox'] = false;
			}
			if (!isset($_POST['setting']['file_id'])) {
				$_POST['setting']['file_id'] = false;
			}

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
			Session::Redirect('/setting/configuration');
		}

		$settings = \Setting::get_as_array();
		$template->assign('settings', $settings);
		if (isset($settings['file_id']) && $settings['file_id'] != 0 && $settings['file_id'] != "") {
			$file = \File::get_by_id((int)$settings['file_id']);
			$template->assign('file', $file);
		}

		\Skin_Email::synchronize();
		$skin_emails = \Skin_Email::get_all();
		$template->assign('skin_emails', $skin_emails);

		\Skin_Pdf::synchronize();
		$skin_pdfs = \Skin_Pdf::get_all();
		$template->assign('skin_pdfs', $skin_pdfs);
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
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.setting';
	}
}
