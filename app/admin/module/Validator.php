<?php
/**
 * Module Validator
 *
 * @author Roan Buysse <roan@tigron.be>
 */

namespace App\Admin\Module;

use \Skeleton\Core\Application\Web\Module;

class Validator extends Module {
	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = false;

    /**
     * Display
     *
     * @return void
     */
    public function display() {
        die('no validator defined');
    }

	/**
	 * Phone number validator method
	 *
	 * @access public
	 */
	public function display_phone_number() {
		try {
			$phone_util = \libphonenumber\PhoneNumberUtil::getInstance();
			$country_code = $_POST['country_code'];
			$phone_number = $_POST['number'];
			$input_name = $_POST['name'];
			$new_input_name = preg_replace('/\[(\w*)\]$/', '[$1_formatted]', $input_name, 1);
			$number_object = $phone_util->parse($phone_number, $country_code);

			// Formatting
			$formatted_number = $phone_util->format($number_object, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
			$formatted_number_db = $phone_util->format($number_object, \libphonenumber\PhoneNumberFormat::E164);

			$result = [
				'valid' => $phone_util->isValidNumber($number_object),
				'formatted' => $formatted_number,
				'formatted_db' => $formatted_number_db,
				'name' => $new_input_name
			];
		} catch (\libphonenumber\NumberParseException $e) {
			$result = [
				'valid' => false
			];
		}

        echo \json_encode($result);
    }
}
