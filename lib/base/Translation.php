<?php
/**
 * Translation class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Translation {

	/**
	 * Translation
	 *
	 * @access private
	 * @var Translation $translation
	 */
	private static $translation = null;

	/**
	 * Language
	 *
	 * @access private
	 * @var Language $language
	 */
	private $language = null;

	/**
	 * Application
	 *
	 * @access private
	 * @var string 	$application
	 */
	private $application = null;

	/**
	 * Strings
	 *
	 * @access private
	 * @var array $strings
	 */
	private $strings = array();


	/**
	 * Constructor
	 *
	 * @access public
	 * @param Language $language
	 * @param string $application
	 */
	public function __construct(Language $language, $application) {
		$this->application = $application;
		$this->language = $language;
		$this->reload_po_file($language);
		$this->load_strings();
	}

	/**
	 * Translate a string
	 *
	 * @access public
	 * @param string $string
	 * @return string $string
	 */
	public function translate_string($string) {
		$config = Config::Get();
		if ($this->language->name_short == $config->base_language) {
			return $string;
		}
		if (!isset($this->strings[$string]) OR $this->strings[$string] == '') {
			return '[NT]' . $string;
		}

		return $this->strings[$string];
	}

	/**
	 * Set the language for the translation
	 *
	 * @access public
	 * @param Language $language
	 * #param string $application
	 * @return Translation $translation
	 */
	public static function configure(Language $language, $application) {
		self::$translation = new Translation($language, $application);
		return self::$translation;
	}

	/**
	 * Get a translation object
	 *
	 * @access public
	 * @return Translation $translation
	 */
	public static function get() {
		if (!isset(self::$translation)) {
			throw new Exception('Language not set');
		}

		return self::$translation;
	}

	/**
	 * Translate a string
	 *
	 * @access public
	 * @return string $translated_string
	 * @param string $string
	 */
	public static function translate($string) {
		$translation = Translation::Get();
		return $translation->translate_string($string);
	}

	/**
	 * Translate a plural string
	 *
	 * @access public
	 * @return string $translated_string
	 * @param string $string
	 */
	public static function translate_plural($string) {
		$translation = Translation::Get();
		return $translation->translate_string($string);
	}

	/**
	 * Read the po files
	 *
	 * @access public
	 */
	private function reload_po_file() {
		if (file_exists(PO_PATH . '/' . $this->language->name_short . '/' . $this->application . '.po') AND file_exists(TMP_PATH . '/languages/' . $this->language->name_short . '/' . $this->application . '.php')) {
			$po_file_modified = filemtime(PO_PATH . '/' . $this->language->name_short . '/' . $this->application . '.po');
			$array_modified = filemtime(TMP_PATH . '/languages/' . $this->language->name_short . '/' . $this->application . '.php');

			if ($array_modified >= $po_file_modified) {
				return;
			}
		}

		$po_strings = Util::po_load(PO_PATH . '/' . $this->language->name_short . '/' . $this->application . '.po');

		if (!file_exists(TMP_PATH . '/languages/' . $this->language->name_short)) {
			mkdir(TMP_PATH . '/languages/' . $this->language->name_short, 0755, true);
		}

		file_put_contents(TMP_PATH . '/languages/' . $this->language->name_short . '/' . $this->application . '.php', '<?php $strings = ' . var_export($po_strings, true) . '?>');
	}

	/**
	 * Load the strings
	 *
	 * @access private
	 */
	private function load_strings() {
		if (file_exists(TMP_PATH . '/languages/' . $this->language->name_short . '/' . $this->application . '.php')) {
			require TMP_PATH . '/languages/' . $this->language->name_short . '/' . $this->application . '.php';
			$this->strings = $strings;
		}
	}
}
