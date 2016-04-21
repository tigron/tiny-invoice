<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\File\Picture\Picture;

class Web_Module_File extends Module {
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
			$file = File::get_by_id($_GET['id']);
		} catch (Exception $e) {
			$this->display_404();
		}
		$file->client_download();
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
