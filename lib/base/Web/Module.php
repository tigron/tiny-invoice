<?php
/**
 * Module startup and handling
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

abstract class Web_Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @var $template
	 */
	protected $template = null;

	/**
	 * Accept request and dispatch it to the module
	 *
	 * @access public
	 */
	public function accept_request() {
		/**
		 * Initialize sticky sessions
		 */
		Web_Session_Sticky::clear(get_class($this));
		$session = Web_Session_Sticky::Get();
		$session->module = get_class($this);

		/**
		 * Pre-boot actions
		 **/
		if (Application::Get()->name == 'admin') {
			$this->pre_admin();
		}

		$template = Web_Template::Get();
		$template->assign('session', $_SESSION);
		$template->assign('MODULE', get_class($this));

		if (method_exists($this, 'secure')) {
			$allowed = $this->secure();
			if (!$allowed) {
				throw new Exception('Possible security breach');
			}
		}

		if (isset($_REQUEST['action']) AND method_exists($this, 'display_' . $_REQUEST['action'])) {
			$template->assign('action', $_REQUEST['action']);
			call_user_func(array($this, 'display_'.$_REQUEST['action']));
		} else {
			$this->display();
		}

		if ($this->template !== null and $this->template != false) {
			$template->display($this->template);
		}
	}

	/**
	 * Pre-admin function
	 */
	private function pre_admin() {
		if (!isset($_SESSION['user']) AND $this->login_required === true) {
			Web_Session::Redirect('/login');
		}
		$template = Web_Template::Get();
		$template->assign('settings', Setting::get_as_array());
	}

	/**
	 * Display method
	 *
	 * All requests will be handled by this method
	 *
	 * @access public
	 */
	abstract public function display();
}
