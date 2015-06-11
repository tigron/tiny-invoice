<?php
/**
 * Mail Template class
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Email_Template {

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
	 * Template
	 *
	 * @var Template $template
	 */
	private $template = null;

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param Language $language
	 */
	public function __construct($name, Language $language) {
		$this->name = $name;
		$this->language = $language;

		$this->template = new Template();
		$this->template->set_template_directory(STORE_PATH . '/email/template');
		$this->template->set_translation(Translation::Get($this->language, 'email'));
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
		foreach ($this->variables as $key => $value) {
			$this->template->assign($key, $value);
		}

		return $this->template->render($this->name . '/' . $type . '.twig');
	}
}
