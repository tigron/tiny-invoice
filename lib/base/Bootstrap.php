<?php
/**
 * Bootstrap Class
 *
 * Initializes the Skeleton framework
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Bootstrap {

	/**
	 * Bootstrap
	 *
	 * @access public
	 */
	public static function boot() {

		date_default_timezone_set('Europe/Brussels');

		/**
		 * Set the root path
		 */
		$root_path = realpath(dirname(__FILE__) . '/../..');

		/**
		 * Register the autoloader from Composer
		 */
		require_once $root_path . '/lib/external/packages/autoload.php';

		/**
		 * Get the config
		 */
		if (!file_exists($root_path . '/config/Config.php')) {
			echo 'Please create your Config.php file' . "\n";
			exit(1);
		}

		require_once $root_path . '/config/Config.php';
		$config = Config::Get();

		/**
		 * Register the autoloader
		 */
		$autoloader = new \Skeleton\Core\Autoloader();
		$autoloader->add_include_path($root_path . '/lib/model/');
		$autoloader->add_include_path($root_path . '/lib/base/');
		$autoloader->register();

		/**
		 * Initialize the database
		 */
		$database = \Skeleton\Database\Database::Get($config->database, true);

		/**
		 * Check if Seta is loaded
		 */
		try {
			$setting = Setting::get_by_name('setasign_pdf_extractor');
		} catch (Exception $e) {
			$setting = new Setting();
			$setting->name = 'setasign_pdf_extractor';
			$setting->value = 0;
		}

		if (file_exists($root_path . '/lib/component/SetaPDF/Autoload.php')) {
			require_once $root_path . '/lib/component/SetaPDF/Autoload.php';
			$setting->value = 1;
		} else {
			$setting->value = 0;
		}

		try {
			$setting->save();
		} catch (Exception $e) { }

		/**
		 * Initialize the file store
		 */
		\Skeleton\File\Config::$file_dir = $root_path . '/store/file/';

		/**
		 * Initialize the thumbnail cache, define thumbnail formats
		 */
		\Skeleton\File\Picture\Config::$tmp_dir = $root_path . '/tmp/picture/';
		\Skeleton\File\Picture\Config::add_resize_configuration('document_preview', 328, 600);
		\Skeleton\File\Picture\Config::add_resize_configuration('incoming_preview', 240, 600);

		/**
		 * Initialize the application directory
		 */
		\Skeleton\Core\Config::$application_dir = $root_path . '/app/';
		\Skeleton\Core\Config::$asset_dir = $root_path . '/lib/external/assets/';

		/**
		 * Initialize the error handler
		 */
		\Skeleton\Error\Config::$debug = true;
		\Skeleton\Error\Handler::enable();

		/**
		 * Initialize the translations
		 */
		\Skeleton\I18n\Config::$po_directory = $root_path . '/po/';
		\Skeleton\I18n\Config::$cache_directory = $root_path . '/tmp/languages/';

		/**
		 * Initialize the template caching path
		 */
		\Skeleton\Template\Twig\Config::$cache_directory = $root_path . '/tmp/twig/';

		/**
		 * Set the email path
		 */
		\Skeleton\Email\Config::$email_directory = $root_path . '/store/email/';
		\Skeleton\Email\Config::$archive_mailbox = $config->archive_mailbox;

		/**
		 * Set the database migration path
		 */
		\Skeleton\Database\Migration\Config::$migration_directory = $root_path . '/migrations/';
		\Skeleton\Database\Migration\Config::$version_storage  = 'database';  // Version will be stored in a database
		\Skeleton\Database\Migration\Config::$database_table  = 'db_version'; // Version will be stored in this database table

		/**
		 * Sticky pager
		 */
		\Skeleton\Pager\Config::$sticky_pager = true;

	}
}
