<?php
/**
 * Twig Template class
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once LIB_PATH . '/base/Template/Twig/Extension/I18n/Tigron.php';
require_once LIB_PATH . '/base/Template/Twig/Extension/Default.php';

class Template_Twig {
	/**
	 * Local Twig instance
	 *
	 * @var Twig_Environment $twig
	 */
	private $twig = null;

	/**
	 * Variables
	 *
	 * @access private
	 * @var array $variables
	 */
	private $variables = [];

	/**
	 * Translation
	 *
	 * @access private
	 * @var Translation $translation
	 */
	private $translation = null;

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param Language $language
	 */
	public function __construct() {
		Twig_Autoloader::register();
		$chain_loader = new Twig_Loader_Chain([
			new Twig_Loader_Filesystem(),
			new Twig_Loader_String()
		]);

		$this->twig = new Twig_Environment(
			$chain_loader,
			[
				'cache' => TMP_PATH . '/twig/',
				'auto_reload' => true,
				'debug' => true
			]
		);

		$this->twig->addExtension(new Twig_Extensions_Extension_I18n_Tigron());
		$this->twig->addExtension(new Template_Twig_Extension_Default());
		$this->twig->addExtension(new Twig_Extension_Debug());
		$this->twig->addExtension(new Twig_Extension_StringLoader());
		$this->twig->getExtension('core')->setNumberFormat(2, '.', '');
	}

	/**
	 * Set Template dir
	 *
	 * @access public
	 * @param string $directory
	 */
	public function set_template_directory($directory) {
		$loader = new Twig_Loader_Filesystem($directory);
		$this->twig->setLoader($loader);
	}

	/**
	 * Assign variables to the template
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function assign($key, $value) {
		$this->variables[$key] = $value;
	}

	/**
	 * Set translation
	 *
	 * @access public
	 * @param Translation $translation
	 */
	public function set_translation(Translation $translation) {
		$this->translation = $translation;
	}

	/**
	 * Render
	 *
	 * @access public
	 * @param string $template
	 * @return string $html
	 */
	public function render($template) {
		$variables = [
			'post' => $_POST,
			'get' => $_GET,
			'cookie' => $_COOKIE,
			'server' => $_SERVER,
			'translation' => $this->translation,
			'language' => 	$this->translation->language,
		];
		if (isset($_SESSION)) {
			$variables['session'] = $_SESSION;
			$variables['session_sticky'] = Web_Session_Sticky::Get();
		}

		$this->twig->addGlobal('env', $variables);
		return $this->twig->render($template, $this->variables);
	}
}