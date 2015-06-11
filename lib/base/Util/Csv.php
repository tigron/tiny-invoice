<?php
/**
 * Util_CSV class
 *
 * Contains CSV utils
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Util_CSV {

	/**
	 * Parse a csv into an array
	 *
	 * @access public
	 * @param string $csv
	 * @return array $csv
	 */
	public static function fetch($csv, $delimiter = ',') {
		$lines = str_getcsv($csv, "\n");
		$data = array();
		foreach ($lines as $line) {
			$row = str_getcsv($line, $delimiter);
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Fetch Assoc
	 *
	 * @access public
	 * @param string $csv
	 * @param string $delimiter
	 * @return array $csv
	 */
	public static function fetch_assoc($csv, $delimiter = ',') {
		$csv = self::trim($csv, $delimiter);
		$lines = explode("\n", $csv);
		$header = explode($delimiter, array_shift($lines));

		foreach ($header as $key => $title) {
			$header[$key] = trim($title);
		}

		$data = array();
		foreach ($lines as $linenumber => $line) {
			$parts = str_getcsv($line, $delimiter);

			if (count($parts) != count($header)) {
				throw new Exception('Error in CSV on line ' . ($linenumber+2) . ': Incorrect number of fields, line: ' . $line);
			}

			$row = array();
			foreach ($parts as $key => $part) {
				$row[$header[$key]] = $part;
			}
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Remove empty lines
	 *
	 * @access public
	 * @param string $csv
	 * @return string $csv
	 */
	public static function trim($csv, $delimiter = ',') {
		$csv = self::fetch(trim($csv), $delimiter);

		$result = '';
		foreach ($csv as $row) {
			$filled = false;

			foreach ($row as $key => $field) {
				$line = '';
				if (trim($field) != '') {
					$filled = true;

					if (strpos($field, $delimiter) !== false) {
						$row[$key] = '"' . $field . '"';
					}
				}
			}

			if ($filled) {
				$result .= implode($delimiter, $row) . "\n";
			}
		}
		return trim($result);
	}

	/**
	 * Detect delimiter
	 *
	 * @access public
	 * @param string $csv
	 * @return string $csv
	 */
	public static function detect_delimiter($csv) {
		$possible_delimiters = array(',', ';',"\t", '|');

		$occurences = 0;
		$delimiter = '';

		foreach ($possible_delimiters as $possible_delimiter) {
			$count = substr_count($csv, $possible_delimiter);
			if ($count > $occurences) {
				$occurences = $count;
				$delimiter = $possible_delimiter;
			}
		}

		return $delimiter;
	}
}
