<?php
/**
 * Web Module User
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Administrative\User;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;
use \Skeleton\Pager\Web\Pager;

use Endroid\QrCode\QrCode;

class Mobile extends Module {
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
	protected $template = 'administrative/user/mobile.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$user = \User::get_by_id($_GET['id']);
		$mobiles = \Mobile::get_registered_by_user($user);
		
		$template = Template::get();
		$template->assign('user', $user);
		$template->assign('mobiles', $mobiles);
	}
	
	/**
	 * Register a new \mobile
	 *
	 * @access public
	 */
	public function display_register() {
		$user = \User::get_by_id($_GET['id']);
		$mobile = new \Mobile();
		$mobile->user_id = $user->id;
		$mobile->save();
		$mobile->create_token();
		
		$template = Template::get();
		$template->assign('mobile', $mobile);
		$this->template = 'administrative/user/mobile/register.twig';
	}
	
	/**
	 * Delete a new \mobile
	 *
	 * @access public
	 */
	public function display_delete() {
		$mobile = \Mobile::get_by_id($_GET['mobile_id']);
		$mobile->delete();
		
		Session::redirect('/administrative/user/mobile?id=' . $mobile->user_id);
	}	
	
	/**
	 * Qr code
	 *
	 * @access public
	 */
	public function display_qr() {
		$qrCode = new QrCode($_GET['code']);

		header('Content-Type: '.$qrCode->getContentType());
		echo $qrCode->writeString();
		$this->template = false;	
	}

	/**
	 * Poll
	 *
	 * @access public
	 */
	public function display_poll() {
		$mobile = \Mobile::get_by_id($_GET['mobile_id']);

		if ($mobile->registered !== null) {
			echo json_encode(true);
		} else {
			echo json_encode(false);
		}
		$this->template = false;
	}	

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.user';
	}
}
