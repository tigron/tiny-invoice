<?php
/**
 * Vat_Check class
 *
 * Checks VAT numbers against the European VIES database and calls
 * VAT_Cache_Check to store the checked numbers in a local cache
 * to avoid overloading the webservice.
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @version $Id$
 */

class Vat_Check {
	/**
	 * Validate a VAT number against the VIES or cached data
	 *
	 * For VIES, please see http://ec.europa.eu/taxation_customs/vies/
	 *
	 * @param string The VAT number to check
	 * @param Country The country the VAT number is from
	 * @return bool Valid or not
	 */
	public static function check($number, Country $country) {
		//1. Check if the vat is a european vat-number
		if ($country->european != 1) {
			return true;
		}

		//2. Check syntax
		if (!self::check_syntax($number, $country)) {
			return false;
		}

		//4. Check if vat-number is in cache
		try {
			$vat_cache = Vat_Check_Cache::get_by_number_country($number, $country);
			if ($vat_cache->valid == 1) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $e) { }

		//5. Check online (VIES-server)
		$result = Vat_Check::check_online($number, $country);
		if ($result['save'] === true) {
			$vat_cache = new Vat_Check_Cache();
			$vat_cache->number = $number;
			$vat_cache->country = $country;
			$vat_cache->valid = $result['result'];
			$vat_cache->save();
		}
		return $result['result'];
	}

	/**
	 * Check the syntax of a VAT number
	 *
	 * @access public
	 * @param int $number
	 * @param Country $country
	 */
	public static function check_syntax($number, Country $country) {
		$config = Config::Get();
		$regexps = $config->regexp_vat;

		if (isset($regexps[$country->vat])) {
			if (!preg_match($regexps[$country->vat], $number)) {
				return false;
			} else {
				return true;
			}
		}

		if (!preg_match('/^[a-zA-Z0-9.]{2,20}$/', $number)) {
			return false;
		}

		if (strlen($number) < 6 OR strlen($number) > 20) {
			return false;
		}
		return true;
	}

	/**
	 * Try to check the VAT number online
	 *
	 * @param string The VAT number
	 * @param Country The country the VAT number is from
	 * @return bool
	 */
	public static function check_online($number, Country $country) {
		$continue = true;
		$retry = 3;

		while ($continue == true) {
			try {
				$result = Vat_Check::check_online_call($number, $country);
				$continue = false;
				$save = true;
				$reachable = true;
			} catch (Exception $e) {
				$retry--;

				if ($retry == false) {
					$continue = false;
					$result = false;
					$save = false;
					$reachable = false;
				}
			}
		}

		return array('result' => $result, 'save' => $save, 'reachable' => $reachable);
	}

	/**
	 * Try to check the VAT number online against the VIES databaseµ
	 * This method should ALWAYS be used in a try {} catch {}, because
	 * an Exception can be thrown at any time when the webservice is
	 * not available.
	 *
	 * For VIES, please see http://ec.europa.eu/taxation_customs/vies/
	 *
	 * @param string The VAT number
	 * @param Country The country the VAT number is from
	 * @throws Exception Throws an exception when the service can't be reached
	 * @return bool
	 */
	public static function check_online_call($number, Country $country) {
		// The @ is to suppress the warnings triggered by the SOAP client when the URL is not reachable
		// An exception is also thrown, which is catched higher in the stack
		$client = @new SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
		$params = array('countryCode' => $country->vat, 'vatNumber' => $number);
		$result = $client->checkVat($params);

		if ($result->valid == 1) {
			return true;
		} else {
			return false;
		}
	}

}