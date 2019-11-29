<?php
/**
 * Module Setting Mailscanner
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Ddeboer\Imap\Server;
use \Ddeboer\Imap\Exception\AuthenticationFailedException;

class Web_Module_Setting_Mailscanner extends Module {
	/**
	 * Login required
	 *
	 * @access protected
	 * @var bool $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'setting/mailscanner.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Template::get();

		if (isset($_POST['setting'])) {
			if (!isset($_POST['setting']['mailscanner_archive'])) {
				$_POST['setting']['mailscanner_archive'] = false;
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
			Session::redirect('/setting/mailscanner');
		}

		try {
			$mailscanner_host = Setting::get_by_name('mailscanner_host')->value;
			$mailscanner_username = Setting::get_by_name('mailscanner_username')->value;
			$mailscanner_password = Setting::get_by_name('mailscanner_password')->value;

			$server = new Server($mailscanner_host, 993, '/imap/ssl/novalidate-cert');
			$server->authenticate($mailscanner_username, $mailscanner_password);

			$template->assign('imap_status', 'ok');
		} catch (\Exception $e) {
			$template->assign('imap_status', 'nok');
		}

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
