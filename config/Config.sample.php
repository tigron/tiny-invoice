<?php
/**
 * Configuration Class
 *
 * Implemented as singleton (only one instance globally).
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Config {
	/**
	 * Config array
	 *
	 * @var array
	 * @access private
	 */
	protected $config_data = [];

	/**
	 * Config object
	 *
	 * @var Config
	 * @access private
	 */
	private static $config = null;

	/**
	 * Private (disabled) constructor
	 *
	 * @access private
	 */
	public function __construct() {
		$this->config_data = array_merge($this->read(), $this->config_data);
	}

	/**
	 * Get config vars as properties
	 *
	 * @param string name
	 * @return mixed
	 * @throws Exception When accessing an unknown config variable, an Exception is thrown
	 * @access public
	 */
	public function __get($name) {
		if (!isset($this->config_data[$name])) {
			throw new Exception('Attempting to read unkown config key: '.$name);
		}
		return $this->config_data[$name];
	}

	/**
	 * Get function, returns a Config object
	 *
	 * @return Config
	 * @access public
	 */
	public static function Get() {
		if (!isset(self::$config)) {
			try {
				self::$config = Application::Get()->config;
			} catch (Exception $e) {
				return new Config();
			}
		}
		return self::$config;
	}

	/**
	 * Check if config var exists
	 *
	 * @param string key
	 * @return bool $isset
	 * @access public
	 */
	public function __isset($key) {
		if (!isset($this->config_data) OR $this->config_data === null) {
			$this->read();
		}

		if (isset($this->config_data[$key])) {
			return true;
		}

		return false;
	}

	/**
	 * Read config file
	 *
	 * Populates the $this->config var, now the config is just in this function
	 * but it could easily be replaced with something else
	 *
	 * @access private
	 */
	private function read() {
		return [
			/**
			 * APPLICATION SPECIFIC CONFIGURATION
			 *
			 * The following configuration items needs to be overwritten in the application config file
			 */

			/**
			 * The hostname to listen on
			 */
			'hostnames' => [],

			/**
			 * Routes
			 */
			'routes' => [],

			/**
			 * Default language. Used for sending mails when the language is not given
			 */
			'default_language' => 'en',

			/**
			 * Default module
			 */
			'module_default' => 'index',

			/**
			 * 404_module
			 */
			'module_404' => '404',

			/**
			 * Sticky pager
			 */
			'sticky_pager' => false,

			/**
			 * GENERAL CONFIGURATION
			 *
			 * These configuration items can be overwritten by application specific configuration.
			 * However they are probably the same for all applications.
			 */

			/**
			 * Setting debug to true will enable debug output and error display.
			 * Error email is not affected.
			 */
			'debug' => true,
			'errors_from' => 'errors@example.com',
			'errors_to' => 'errors@example.com',

			/**
			 * Database
			 */
			'database' => 'mysqli://username:password@localhost/database',

			/**
			 * Translation base language that the templates will be made up in
			 * Do not change after creation of your project!
			 */
			'base_language' => 'en',

			/**
			 * The default language that will be shown to the user if it can not be guessed
			 */
			'default_language' => 'en',

			/**
			 * Items per page
			 */
			'items_per_page' => 20,

			/**
			 * Picture formats
			 *
			 * Array containing all possible picture formats
			 */
			'picture_formats' => [
				'document_preview'	=> [
					'height' => 600,
					'width' => 328
				],
			],

			/**
			 * Archive mailbox
			 */
			'archive_mailbox' => '',

			/**
			 * Known VAT regexes
			 */
			'regexp_vat' => [
					'AT' => '/^U[0-9]{8}$/',
					'BE' => '/^[0-9]{10}$/',
					'BG' => '/^[0-9]{9,10}$/',
					'CY' => '/^[0-9a-zA-Z]{9}$/',
					'CZ' => '/^[0-9]{8,10}$/',
					'DE' => '/^[0-9]{9}$/',
					'DK' => '/^[0-9]{8}$/',
					'EE' => '/^[0-9]{9}$/',
					'EL' => '/^[0-9]{9}$/',
					'ES' => '/^[0-9a-zA-Z]{9}$/',
					'FI' => '/^[0-9]{8}$/',
					'FR' => '/^[a-zA-Z0-9]{2}[0-9]{9}$/',
					'HU' => '/^[0-9]{8}$/',
					'IE' => '/^[0-9a-zA-Z]{9}$/',
					'IT' => '/^[0-9]{11}$/',
					'LT' => '/^[0-9]{9,12}$/',
					'LU' => '/^[0-9]{8}$/',
					'LV' => '/^[0-9]{11}$/',
					'MT' => '/^[0-9]{8}$/',
					'NL' => '/^[a-zA-Z0-9]{12}$/',
					'PL' => '/^[0-9]{10}$/',
					'PT' => '/^[0-9]{9}$/',
					'RO' => '/^[0-9]{2,10}$/',
					'SE' => '/^[0-9]{12}$/',
					'SI' => '/^[0-9]{8}$/',
					'SK' => '/^[0-9]{10}$/',
			],

			/**
			 * VAT examples
			 */
			'example_vat' => [
					'AT' => 'U12345678',
					'BE' => '0123456789',
					'BG' => '123456789',
					'CY' => '12345678L',
					'CZ' => '123456789',
					'DE' => '123456789',
					'DK' => '12345678',
					'EE' => '123456789',
					'EL' => '123456789',
					'ES' => 'X9999999X',
					'FI' => '12345678',
					'FR' => '12123456789',
					'GB' => '123123412',
					'HU' => '12345678',
					'IE' => '1S23456L',
					'IT' => '12345678901',
					'LT' => '123456789',
					'LU' => '12345678',
					'LV' => '12345678901',
					'MT' => '12345678',
					'NL' => '123412123B12',
					'PL' => '1234567890',
					'PT' => '123456789',
					'RO' => '1234567890',
					'SE' => '123456789012',
					'SI' => '12345678',
					'SK' => '1234567890',
			],

		];
	}
}
