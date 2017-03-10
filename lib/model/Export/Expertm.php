<?php
/**
 * Export_Expertm_User class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

abstract class Export_Expertm extends Export {

	/**
	 * Numeric
	 *
	 * @access protected
	 * @param int $size
	 * @param string $field
	 */
	protected function num($size, $field) {
		return str_pad($field, $size, '0', STR_PAD_LEFT);
	}

	/**
	 * Alfa-Numeric
	 *
	 * @access protected
	 * @param int $size
	 * @param string $field
	 */
	protected function alf($size, $field) {
		$field = $this->clean_string($field);
		$field = iconv("UTF-8", "ASCII//TRANSLIT", $field);
		return substr(str_pad($field, $size, ' '), 0, $size);
	}

	/**
	 * Clean string
	 *
	 * @access public
	 * @param string $string
	 * @return string $clean_string
	 */
	protected function clean_string($string) {
		$string = str_replace('.', '', $string);
		$string = str_replace('@', '', $string);
		$string = str_replace('&', '', $string);
		$string = str_replace('-', '', $string);
		$string = str_replace('_', '', $string);
		$string = str_replace('!', '', $string);
		$string = str_replace(',', '', $string);
		$string = str_replace('?', '', $string);
		$string = str_replace("\"", '', $string);
		$string = str_replace(';', '', $string);
		$string = str_replace('*', '', $string);
		$string = str_replace('\'', '', $string);
		$string = str_replace('é', 'e', $string);
		$string = str_replace('è', 'e', $string);
		$string = str_replace('î', 'i', $string);
		$string = str_replace('ë', 'e', $string);
		$string = str_replace('ä', 'a', $string);
		$string = str_replace('â', 'a', $string);
		$string = str_replace('à', 'a', $string);
		$string = str_replace('ç', 'c', $string);
		$string = str_replace('ô', 'o', $string);
		$string = str_replace('ö', 'o', $string);
		$string = str_replace("\r\n", ' ', $string);
		$string = str_replace("\n", ' ', $string);
		$string = trim($string);
		return $string;
	}

	/**
	 * Currency
	 *
	 * @access protected
	 * @param int $size
	 * @param string $val
	 */
	protected function cur($size, $val) {
		$val = sprintf('%0'.($size-5).'.4f', $val);
		$valarr = explode('.', $val);
		$val = str_pad($valarr[0], $size-5, '0', STR_PAD_LEFT);
		$val .= str_pad($valarr[1], 4, '0', STR_PAD_RIGHT).'+';
		$val = str_replace('.', '', $val);
		return $val;
	}

	/**
	 * Boekhoudperiode
	 *
	 * @access protected
	 * @param Date $date
	 * @return string $boekhoudperiode
	 */
	protected function boekhoudperiode($date) {
		try {
			$bookkeeping_start_month = Setting::get_by_name('bookkeeping_start_month')->value;
		} catch (Exception $e) {
			$setting = new Setting();
			$setting->name = 'bookkeeping_start_month';
			$setting->value= 1;
			$setting->save();
			$bookkeeping_start_month = $setting->value;
		}

		$month = $bookkeeping_start_month;
		$periode = 1;
		$boekhoudperiodes = [];

		for ($i = 1; $i <=12; $i++) {
			$boekhoudperiodes[$month] = $periode;

			$month++;
			if ($i % 3 == 0) {
				$periode ++;
			}
		}

		$cleaned = [];
		foreach ($boekhoudperiodes as $month => $periode) {
			if ($month > 12) {
				$month -= 12;
			}
			$cleaned[$month] = $periode;
		}

		ksort($cleaned);
		return $cleaned[ date('n', strtotime($date)) ];
	}

	/**
	 * BTWmaand
	 *
	 * @access protected
	 * @param Date $date
	 * @return string $btwmaand
	 */
	protected function btwmaand($date) {
		$year = date('Y', strtotime($date));

		try {
			$bookkeeping_vat_period = Setting::get_by_name('bookkeeping_vat_period')->value;
		} catch (Exception $e) {
			$setting = new Setting();
			$setting->name = 'bookkeeping_vat_period';
			$setting->value= 'month';
			$setting->save();
			$bookkeeping_vat_period = $setting->value;
		}

		if ($bookkeeping_vat_period == 'month') {
			$btw_maand = $year . date('m', strtotime($date));
		} else {
			switch (date('n', strtotime($date))) {
				case 1:
				case 2:
				case 3:	$maand = 1; break;
				case 4:
				case 5:
				case 6:	$maand = 2; break;
				case 7:
				case 8:
				case 9:	$maand = 3; break;
				case 10:
				case 11:
				case 12: $maand = 4; break;
			}
			$btw_maand = $year . '0' . $maand;
		}
		return $btw_maand;
	}

}
