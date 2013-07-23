<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/model/Picture.php';

class Module_Picture extends Web_Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = false;

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = false;

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		if (!isset($_GET['id'])) {
			$this->display_404();
		}

		try {
			$picture = Picture::get_by_id($_GET['id']);
		} catch (Exception $e) {
			$this->display_404();
		}
		if (isset($_GET['size'])) {
			$picture->show($_GET['size']);
		} else {
			$picture->show();
		}
	}

	/**
	 * Show 404
	 *
	 * @access private
	 */
	private function display_404() {
		header("HTTP/1.0 404 Not Found");
		echo '404: picture not found';
		exit;
	}
}
