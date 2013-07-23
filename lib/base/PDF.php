<?php
/**
 * PDF class
 *
 * Create PDF documents
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once EXT_PATH . '/packages/vendor/autoload.php';
require_once EXT_PATH . '/dompdf_config.inc.php';
require_once LIB_PATH . '/base/PDF/Template.php';
require_once LIB_PATH . '/base/File/Store.php';

class PDF {
	/**
	 * PDF type
	 *
	 * @access private
	 * @var string $type
	 */
	private $type = '';

	/**
	 * PDF settings
	 *
	 * @access private
	 * @var array $settings
	 */
	private $settings = array();

	/**
	 * Language
	 *
	 * @access private
	 * @var Language $language
	 */
	private $language = null;

	/**
	 * Assigned variables
	 *
	 * @access private
	 * @var array $assigns
	 */
	private $assigns = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $type
	 */
	public function __construct($type, Language $language = null, $settings = array('size' => 'A4', 'orientation' => 'portrait')) {
		if ($type === null) {
			throw new Exception('No PDF type specified');
		}

		if ($language == null) {
			$config = Config::get();
			$language = Language::get_by_name_short($config->base_language);
		}

		$this->language = $language;
		$this->type = $type;
		$this->settings = $settings;
	}

	/**
	 * Assign
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public function assign($key, $value) {
		$this->assigns[$key] = $value;
	}

	/**
	 * Render PDF
	 *
	 * @access public
	 */
	public function render($output = 'string', $filename = null) {
		if ($filename == null) {
			$filename = $this->type . '_' . microtime(true) . '.pdf';
		}

		$template = new PDF_Template($this->type, $this->language);
		foreach ($this->assigns as $key => $value) {
			$template->assign($key, $value);
		}

		$html = $template->render('html');

		switch ($output) {
			case 'html':
				return $html;
				break;
			case 'file':
			case 'dompdf':
				$dompdf = new DOMPDF();
				$dompdf->set_base_path(STORE_PATH . '/pdf/media/');
				$dompdf->set_paper($this->settings['size'], $this->settings['orientation']);
				$dompdf->load_html($html);
				$dompdf->render();
				$file = File_Store::store($filename, $dompdf->output());
				return $file;
		}
	}
}
