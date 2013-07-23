<?php
/**
 * Mail Template class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once LIB_PATH . '/base/Translation.php';

class Email_Template {
	/**
	 * Local Twig instance
	 *
	 * @var Twig_Environment $twig
	 */
	private $twig = null;

	/**
	 * Template name
	 *
	 * @var string $name
	 */
	private $name = null;

	/**
	 * Render language
	 *
	 * @var Language $language
	 */
	private $language = null;

	/**
	 * Parameters to assign
	 *
	 * @var array $parameters
	 */
	private $variables = array();

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param Language $language
	 */
	public function __construct($name, Language $language) {
		$this->name = $name;
		$this->language = $language;

		Twig_Autoloader::register();

		$loader_paths[] = STORE_PATH . '/email/template/';
		$loader = new Twig_Loader_Filesystem($loader_paths);

		$this->twig = new Twig_Environment(
			$loader,
			array(
				'cache' => TMP_PATH . '/twig/email/',
				'auto_reload' => true,
			)
		);

		$this->twig->addExtension(
			new Twig_Extensions_Extension_I18n(
				array(
					'function_translation' => 'Translation::translate',
					'function_translation_plural' => 'Translation::translate_plural',
				)
			)
		);
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
	 * Render the template
	 *
	 * @param string $name The name of the email
	 * @param string $type The type of output (html, txt, subject)
	 * @param Language $language The language to render the email in
	 * @return string
	 */
	public function render($type) {
		if (!file_exists(STORE_PATH . '/email/template/' . $this->name . '/' . $type . '.twig')) {
			return '';
		}

		// FIXME
		// Overriding Translation and setting it back is quite ugly :(
		$current_language = Language::Get();
		Translation::configure($this->language, 'email');

		$variables = array(
			'template' => $this,
			'now' => time()
		);
		$this->twig->addGlobal('env', $variables);

		$return = '';
		$twig_template = $this->twig->loadTemplate($this->name . '/' . $type . '.twig');
		$return .= Util::rewrite_reverse_html($twig_template->render($this->variables));

		// If an application was running, fix it
		if (defined('APP_NAME')) {
			Translation::configure($current_language, APP_NAME);
		}
		return $return;
	}
}
