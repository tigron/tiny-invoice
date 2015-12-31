<?php
/**
 * Email class
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Email extends \Skeleton\Email\Email {

	/**
	 * Construct
	 *
	 * @access public
	 * @param string $type
	 */
	public function __construct($type, Language $language = null) {

		/**
		 * Get the email skin
		 */
		try {
			$setting = Setting::get_by_name('skin_email_id');
		} catch (Exception $e) {
			debug_print_backtrace();
			Skin_Email::synchronize();
			$skin_emails = Skin_Email::get_all();
			$setting = new Setting();
			$setting->name = 'skin_email_id';
			$setting->value = array_shift($skin_emails)->id;
			$setting->save();
		}

		$skin_email = Skin_Email::get_by_id($setting->value);

		/**
		 * Set the email path
		 */
		\Skeleton\Email\Config::$email_directory = \Skeleton\Email\Config::$email_directory . '/' . $skin_email->path . '/';

		// Set transation
		if (is_null($language)) {
			$language = Language::get_default();
		}
		$translation = Skeleton\I18n\Translation::get($language, 'email');
		$this->set_translation($translation);

		// Assign company info to email template
		$settings = Setting::get_as_array();
		if (isset($settings['country_id'])) {
			$settings['country'] = Country::get_by_id($settings['country_id']);
		}
		$this->assign('settings', $settings);

		parent::__construct($type, $language);
	}
}
