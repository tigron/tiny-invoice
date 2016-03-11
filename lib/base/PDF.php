<?php
/**
 * PDF class
 *
 * Create PDF documents
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use Dompdf\Dompdf;
use Dompdf\Options;

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

		/**
		 * Get the pdf skin
		 */
		try {
			$setting = Setting::get_by_name('skin_pdf_id');
		} catch (Exception $e) {
			Skin_Pdf::synchronize();
			$skin_pdfs = Skin_Pdf::get_all();
			$setting = new Setting();
			$setting->name = 'skin_pdf_id';
			$setting->value = array_shift($skin_pdfs)->id;
			$setting->save();
		}

		$skin_pdf = Skin_Pdf::get_by_id($setting->value);

		$this->template->set_template_directory(dirname(__FILE__) . '/../../store/pdf/' . $skin_pdf->path . '/template');
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

		$setting = Setting::get_by_name('skin_pdf_id');
		$skin_pdf = Skin_Pdf::get_by_id($setting->value);

		switch ($output) {
			case 'html':
				return $html;
				break;
			case 'file':
			case 'dompdf':
				$options = new Options();
				$options->set([
					'dpi' => 300,
					'isPhpEnabled' => true,
					'fontDir' => dirname(__FILE__) . '/../../store/pdf/' . $skin_pdf->path . '/media/font/fonts/',
					'tempDir' => realpath(dirname(__FILE__) . '/../../tmp/dompdf'),
					'isRemoteEnabled' => true,
					'logOutputFile' => false,
					'isFontSubsettingEnabled' => true
				]);

				$dompdf = new DOMPDF();
				$dompdf->setOptions($options);
				$dompdf->set_base_path(dirname(__FILE__) . '/../../store/pdf/' . $skin_pdf->path . '/media/');
                $dompdf->set_paper($this->configuration['size'], $this->configuration['orientation']);
                $dompdf->load_html($html);
                $dompdf->render();

                $file = File::store($filename, $dompdf->output());
                return $file;
		}
	}
}
