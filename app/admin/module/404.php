<?php
/**
 * Module 404
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Web_Module_404 extends Web_Module {
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
	public $template = null;

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		echo '404';
	}
}
