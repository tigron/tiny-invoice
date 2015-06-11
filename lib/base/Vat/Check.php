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
			// If VIES gave a negative result, check also against the Kruispuntbank
			// for BE-only VAT numbers such as for not for profit organizations
			if ($country->iso2 == 'BE') {
				return self::check_online_be_call($number);
			} else {
				return false;
			}
		}
	}

	/**
	 * Try to check a Belgian VAT number online against the "Kruispuntbank"
	 * This method should only be used as a last resort, since the interface
	 * with the "Kruispuntbank" is a bit dodgy.
	 *
	 * For more information on the "Kruispuntbank", please see http://kbo-bce-ps.economie.fgov.be/
	 *
	 * @param string The VAT number
	 * @throws Exception Throws an exception when the service can't be reached
	 * @return bool
	 */
	public static function check_online_be_call($number) {
		$cookie = TMP_PATH . '/kruispuntbank_cookie.txt';

		$postdata = 'ondernemingsnummer='.$number.'&';
		$postdata .= 'actionEntnr=Zoek+onderneming&';
		$postdata .= 'natuurlijkPersoon=true&';
		$postdata .= '_natuurlijkPersoon=on&';
		$postdata .= 'rechtsPersoon=true&';
		$postdata .= '_rechtsPersoon=on&';
		$postdata .= 'searchWord=&';
		$postdata .= 'pstcdeNPRP=&';
		$postdata .= 'postgemeente1=&';
		$postdata .= 'familynameFonetic=&';
		$postdata .= 'pstcdeNPFonetic=&';
		$postdata .= 'postgemeente2=&';
		$postdata .= 'searchwordRP=&';
		$postdata .= 'pstcdeRPFonetic=&';
		$postdata .= 'postgemeente3=&';
		$postdata .= 'rechtsvormFonetic=ALL&';
		$postdata .= 'familynameExact=&';
		$postdata .= 'firstName=&';
		$postdata .= 'pstcdeNPExact=&';
		$postdata .= 'postgemeente4=&';
		$postdata .= 'firmName=&';
		$postdata .= 'pstcdeRPExact=&';
		$postdata .= 'postgemeente5=&';
		$postdata .= 'rechtsvormExact=ALL';

		// Get to the front page to get the cookie
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://kbopub.economie.fgov.be/kbopub/zoekwoordenform.html?' . $postdata);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6");
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

		ob_start();
			$result = curl_exec($ch);
		ob_end_clean();

		if ($result === false) {
			throw new Exception('Kruispuntbank not available');
		}

		if (strpos($result, 'Ondernemingsgegevens')) {
			return true;
		} else {
			return false;
		}
	}
}
?>