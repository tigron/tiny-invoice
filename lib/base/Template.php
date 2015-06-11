<?php
/**
 * Template class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Template {

	/**
	 * Template directory
	 *
	 * @access private
	 * @var string $templat_directory
	 */
	private $template_directory = null;

	/**
	 * Translation
	 *
	 * @access private
	 * @var Translation $translation
	 */
	private $translation = null;

	/**
	 * Variables
	 *
	 * @access private
	 * @var array $variables
	 */
	protected $variables = array();

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Set Template dir
	 *
	 * @access public
	 * @param string $directory
	 */
	public function set_template_directory($directory) {
		$this->template_directory = $directory;
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
	 * Assign a variable
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public function assign($key, $value) {
		$this->variables[$key] = $value;
	}

	/**
	 * Render
	 *
	 * @access public
	 * @param string $template
	 * @return string $html
	 */
	public function render($template) {
		list($filename, $extension) = explode('.', basename($template));

		switch ($extension) {
			case 'tpl':
				$renderer = new Template_Smarty();
				break;
			case 'twig':
				$renderer = new Template_Twig();
				break;
			default: throw new Exception('Unknown template type');
		}

		// Set the template path
		if ($this->template_directory !== null) {
			$renderer->set_template_directory($this->template_directory);
		} else {
			$renderer->set_template_directory(Application::Get()->template_path);
		}

		// Pass the variables to the template renderer
		foreach ($this->variables as $key => $value) {
			$renderer->assign($key, $value);
		}

		// Set the translation object
		if ($this->translation !== null) {
			$renderer->set_translation($this->translation);
		} else {
			$translation = Translation::Get(Application::Get()->language, Application::Get()->name);
			$renderer->set_translation($translation);
		}

		return $renderer->render($template);
	}
}