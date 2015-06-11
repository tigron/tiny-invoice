<?php
/**
 * PDF Template class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class PDF_Template {

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
	private $variables = [];

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param Language $language
	 */
	public function __construct($name, Language $language) {
		$this->name = $name;
		$this->language = $language;
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
	 * @param string $type The type of output (html, txt, subject)
	 * @return string
	 */
	public function render($type) {
		if (!file_exists(STORE_PATH . '/pdf/template/' . $this->name . '/' . $type . '.twig')) {
			return '';
		}

		$template = new Template();
		$template->set_template_directory(STORE_PATH . '/pdf/template/');
		$template->set_translation(Translation::Get($this->language, 'pdf'));
		foreach ($this->variables as $key => $value) {
			$template->assign($key, $value);
		}
		return $template->render($this->name . '/' . $type . '.twig');
	}
}
