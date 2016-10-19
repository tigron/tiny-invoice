<?php
/**
 * Module Setting Company
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;

class Web_Module_Setting_Api extends Module {
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
	public $template = 'setting/api.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::Get();

		if (isset($_POST['setting'])) {
			if (isset($_POST['setting']['api_public_documents'])) {
				$_POST['setting']['api_public_documents'] = true;
			} else {
				$_POST['setting']['api_public_documents'] = false;
			}

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
			Session::set_sticky('message', 'updated');
			Session::redirect('/setting/api');
		}

		try {
			$tag_ids = Setting::get_by_name('api_document_tag_ids')->value;
		} catch (Exception $e) {
			$tag_ids = '';
		}
		$tag_ids = explode(',', $tag_ids);
		$selected_tags = [];
		foreach ($tag_ids as $tag_id) {
			try {
				$selected_tags[] = Tag::get_by_id($tag_id);
			} catch (Exception $e) { }
		}
		$template->assign('selected_tags', $selected_tags);
		$template->assign('settings', Setting::get_as_array());
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
