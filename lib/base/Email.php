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
		if (isset($settings['vat']) AND isset($settings['country'])) {
			$settings['vat'] = Vat::format($settings['vat'], $settings['country']);
		}
		$this->assign('settings', $settings);

		parent::__construct($type, $language);
	}
}
