<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;

class Web_Module_Mobile_Register extends Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = false;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'mobile/register.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {

	}

	public function display_register() {
		$this->template = false;

		try {
			$mobile = Mobile::get_by_token($_POST['code']);
		} catch (Exception $e) {
			$response = [
				'success' => false,
				'message' => 'Incorrect code',
			];
			echo json_encode($response);
			return;
		}

		$mobile->name = $_POST['name'];
		$mobile->registered = date('Y-m-d H:i:s');
		$mobile->save();

		setcookie('mobile-token', $mobile->token, time() + (10 * 365 * 24 * 60 * 60));

		$response = [
			'success' => true,
			'message' => 'Registered',
		];
		echo json_encode($response);
	}
}
