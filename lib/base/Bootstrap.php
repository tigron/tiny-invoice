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
		\Skeleton\Core\Config::include_directory($root_path . '/config');
		$config = \Skeleton\Core\Config::Get();

		/**
		 * Register the autoloader
		 */
		$autoloader = new \Skeleton\Core\Autoloader();
		if (file_exists($root_path . '/lib/component')) {
			$autoloader->add_include_path($root_path . '/lib/component/');
		}
		$autoloader->add_include_path($root_path . '/lib/model/');
		$autoloader->add_include_path($root_path . '/lib/base/');
		$autoloader->add_include_path($root_path . '/lib/console/');
		$autoloader->register();

		/**
		 * Setasign
		 */
		if (file_exists($root_path . '/lib/component/SetaPDF/Autoload.php')) {
			require_once $root_path . '/lib/component/SetaPDF/Autoload.php';
		}

		/**
		 * Initialize the database
		 */
		$database = \Skeleton\Database\Database::Get($config->database, true);
		\Skeleton\Database\Config::$query_log = false;
		\Skeleton\Database\Config::$query_counter = true;
		\Skeleton\Database\Config::$auto_discard = true;

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
		 * Initialize the error handler
		 */
		\Skeleton\Error\Config::$debug = true;
		\Skeleton\Error\Handler::enable();

		/**
		 * Initialize the translations
		 */
		\Skeleton\I18n\Config::$language_interface = '\Language';
		\Skeleton\I18n\Translator\Storage\Po::set_default_configuration(['storage_path' => $root_path . '/po/']);
		\Skeleton\I18n\Config::$cache_path = $root_path . '/tmp/languages/';

		// PDF Translator
		$storage = new \Skeleton\I18n\Translator\Storage\Po();
		$translator = new \Skeleton\I18n\Translator('pdf');
		$translator->set_translator_storage($storage);
		$translator_extractor_twig = new \Skeleton\I18n\Translator\Extractor\Twig();
		$translator_extractor_twig->set_template_path($root_path . '/store/pdf/');
		$translator->set_translator_extractor($translator_extractor_twig);
		$translator->save();

		// Email Translator
		$storage = new \Skeleton\I18n\Translator\Storage\Po();
		$translator = new \Skeleton\I18n\Translator('email');
		$translator->set_translator_storage($storage);
		$translator_extractor_twig = new \Skeleton\I18n\Translator\Extractor\Twig();
		$translator_extractor_twig->set_template_path($root_path . '/store/email/default');
		$translator->set_translator_extractor($translator_extractor_twig);
		$translator->save();

		/**
		 * Initialize the template caching path
		 */
		\Skeleton\Template\Twig\Config::$cache_path = $root_path . '/tmp/twig/';
		\Skeleton\Template\Twig\Config::add_extension("Template_Twig_Extension_Custom");

		/**
		 * Transaction daemon settings
		 */
		if (isset($config->transaction_pid)) {
			\Skeleton\Transaction\Config::$pid_file = $config->transaction_pid;
		}
		if (isset($config->transaction_monitor)) {
			\Skeleton\Transaction\Config::$monitor_file = $config->transaction_monitor;
		}

		/**
		 * Set the email path
		 */

		/**
		 * Get the email skin
		 */
		$skin_email_setting = null;
		try {
			$skin_email_setting = Setting::get_by_name('skin_email_id');
		} catch (Exception $e) {
			try {
				Skin_Email::synchronize();
				$skin_emails = Skin_Email::get_all();
				$skin_email_setting = new Setting();
				$skin_email_setting->name = 'skin_email_id';
				$skin_email_setting->value = array_shift($skin_emails)->id;
				$skin_email_setting->save();
			} catch (Exception $e) {
				// Database is not installed yet
			}
		}

		if ($skin_email_setting !== null) {
			$skin_email = Skin_Email::get_by_id($skin_email_setting->value);

			/**
			 * Set the email path
			 */
			\Skeleton\Email\Config::$email_directory = $root_path . '/store/email/' . $skin_email->path . '/';
		}


		try {
			$archive_mailbox = Setting::get_by_name('archive_mailbox')->value;
			if (trim($archive_mailbox) != '') {
				\Skeleton\Email\Config::$archive_mailbox = $archive_mailbox;
			}
		} catch (Exception $e) { }

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
