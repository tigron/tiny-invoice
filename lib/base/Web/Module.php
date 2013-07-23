<?php
/**
 * Module startup and handling
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/base/Web/Session/Sticky.php';

abstract class Web_Module {

	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = true;

	/**
	 * Accept request and dispatch it to the module
	 *
	 * @access public
	 */
	public function accept_request() {
		$application = APP_NAME;
		if (is_callable(array($this, 'pre_' . $application))) {
			call_user_func_array(array($this, 'pre_' . $application), array());
		}

		$template = Web_Template::Get();
		$template->surrounding = false;
		$module = get_class($this);
		$module = str_replace('module_', '', strtolower($module));
		$template->add_env('module', $module);

		$session = Web_Session_Sticky::Get();
		$session->module = $module;

		$is_ajax = false;
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$is_ajax = true;
		}
		$_SESSION['is_ajax'] = $is_ajax;

		if (isset($_REQUEST['action']) AND is_callable(array($this, 'display_'.$_REQUEST['action']))) {
			$template->assign('action', $_REQUEST['action']);
			call_user_func_array(array($this, 'display_'.$_REQUEST['action']), array());
		} else {
			$this->display();
		}

		if ($this->template != null) {
			$template->display($this->template);
		}
	}

	/**
	 * Pre-admin function
	 */
	private function pre_admin() {
		if (!isset($_SESSION['user']) AND $this->login_required) {
			Web_Session::Redirect('/login');
		}

		if (isset($_SESSION['user'])) {
			User::Set($_SESSION['user']);
		}

		$config = Config::Get();
		$template = Web_Template::Get();
		$template->assign('company', $config->company_info['company']);
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
