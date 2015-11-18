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
	 * PDF configuration
	 *
	 * @access private
	 * @var array $configuration
	 */
	private $configuration = [];

	/**
	 * Template
	 *
	 * @access private
	 * @var Template $template
	 */
	private $template = null;

	/**
	 * Assigned variables
	 *
	 * @access private
	 * @var array $assigns
	 */
	private $assigns = [];

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $type
	 */
	public function __construct($type, Language $language = null, $configuration = [ 'size' => 'A4', 'orientation' => 'portrait' ]) {
		if ($type === null) {
			throw new Exception('No PDF type specified');
		}

		if ($language == null) {
			$language = Language::get_default();
		}

		$this->type = $type;
		$this->configuration = $configuration;

		$this->template = new \Skeleton\Template\Template();
		$this->template->set_template_directory(dirname(__FILE__) . '/../../store/pdf/template');
		$this->template->set_translation(Skeleton\I18n\Translation::get($language, 'pdf'));

		// Assign company info to pdf template
		$settings = Setting::get_as_array();
		if (isset($settings['country_id'])) {
			$settings['country'] = Country::get_by_id($settings['country_id']);
		}
		$this->template->assign('settings', $settings);
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

		foreach ($this->assigns as $key => $value) {
			$this->template->assign($key, $value);
		}

		$html = $this->template->render($this->type . '/html.twig');

		switch ($output) {
			case 'html':
				return $html;
				break;
			case 'file':
			case 'dompdf':
				$dompdf = new DOMPDF();
				$dompdf->set_base_path(dirname(__FILE__) . '/../../../store/pdf/media/');
                $dompdf->set_paper($this->configuration['size'], $this->configuration['orientation']);
                $dompdf->load_html($html);
                $dompdf->render();

                $file = File::store($filename, $dompdf->output());
                return $file;
		}
	}
}
