<?php
/**
 * PDF class
 *
 * Create PDF documents
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

require_once realpath(dirname(__FILE__) . '/../..') . '/lib/external/dompdf.config.php';

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
			$language = Language::get_by_name_short(Config::get()->default_language);
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

		$template = new \Skeleton\Template\Template();
		$template->set_template_directory(dirname(__FILE__) . '/../../store/pdf/template');

		foreach ($this->assigns as $key => $value) {
			$template->assign($key, $value);
		}

		$html = $template->render($this->type . '/html.twig');

		switch ($output) {
			case 'html':
				return $html;
				break;
			case 'file':
			case 'dompdf':
				$dompdf = new DOMPDF();
				$dompdf->set_base_path(dirname(__FILE__) . '/../../../store/pdf/media/');
                $dompdf->set_paper($this->settings['size'], $this->settings['orientation']);
                $dompdf->load_html($html);
                $dompdf->render();
                $file = File::store($filename, $dompdf->output());
                return $file;
		}
	}
}
