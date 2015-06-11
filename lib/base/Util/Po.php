<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

class Util_Po {

	/**
	 * Load PO File
	 *
	 * @param string $filename
	 * @return array $strings
	 */
	public static function load($filename) {
		$strings = array();
		if (!file_exists($filename)) {
			return array();
		}
		$content = file_get_contents($filename);

		$matched = preg_match_all('/(msgid\s+("([^"]|\\\\")*?"\s*)+)\s+(msgstr\s+("([^"]|\\\\")*?"\s*)+)/',	$content, $matches);

		if (!$matched) {
			return array();
		}
		// get all msgids and msgtrs
		for ($i = 0; $i < $matched; $i++) {
			$msgid = preg_replace('/\s*msgid\s*"(.*)"\s*/s', '\\1', $matches[1][$i]);
			$msgstr= preg_replace('/\s*msgstr\s*"(.*)"\s*/s', '\\1', $matches[4][$i]);

			if (Util::po_prepare_load_string($msgid) !== '') {
				$strings[Util::po_prepare_load_string($msgid)] = Util::po_prepare_load_string($msgstr);
			}
		}

		return $strings;
	}

	/**
	 * Prepare a string for loading
	 *
	 * @access private
	 * @param string $string
	 * @return string $fixed_string
	 */
	public static function prepare_load_string($string) {
		$smap = array('/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\\"/');
		$rmap = array('', "\n", "\r", "\t", '"');
		return (string) preg_replace($smap, $rmap, $string);
	}

	/**
	 * Prepare a string to be written in a po file
	 *
	 * @access private
	 * @param string $string
	 * @return string $fixed_string
	 */
	public static function prepare_save_string($string) {
		$smap = array('"', "\n", "\t", "\r");
		$rmap = array('\\"', '\\n"' . "\n" . '"', '\\t', '\\r');
		return (string) str_replace($smap, $rmap, $string);
	}

	/**
	 * Save a po file, based on a translation array
	 *
	 * @access public
	 * @param string $filename
	 * @param array $strings
	 */
	public static function save($filename, $project, $language, $strings) {
		ksort($strings);
		$dir = dirname($filename);
		if (!file_exists($dir)) {
			mkdir($dir, 0755, true);
		}

		// Guess the language string
		switch ($language->name_short) {
			case 'de':
			case 'es':
			case 'fr':
			case 'nl':
			case 'it':
			case 'fi':
				$language_string = $language->name_short;
				break;
			case 'se':
				$language_string = 'sv';
				break;
			case 'zh-chs':
				$language_string = 'zh_Hans';
				break;
			default:
				$language_string = 'unknown';
		}

		$output = '';

		// Generate the file header
		$output .= '# .po file for project "' . $project . '" in ' . $language->name . "\n";
		$output .= '' . "\n";
		$output .= 'msgid ""' . "\n";
		$output .= 'msgstr ""' . "\n";
		$output .= '"Project-Id-Version: ' . $project . '-' . $language_string . '.' . time() . '\n"' . "\n";
		$output .= '"PO-Revision-Date: ' . date('Y-m-d H:iO')  . '\n"' . "\n";
		$output .= '"MIME-Version: 1.0\n"' . "\n";
		$output .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
		$output .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";
		$output .= '"Language: ' . $language_string . '\n"' . "\n";

		$output .= "\n";

		foreach ($strings as $key => $value) {
			$output .= 'msgid "' . Util::po_prepare_save_string($key) . '"' . "\n";
			$output .= 'msgstr "' . Util::po_prepare_save_string($value) . '"' . "\n\n";
		}

		file_put_contents($filename, $output);
	}

	/**
	 * Merge 2 po files
	 *
	 * @access public
	 * @param array $strings1
	 * @param array $strings2
	 */
	public static function merge($base, $extra) {
		$base_strings = Util::po_load($base);
		$extra_strings = Util::po_load($extra);

		$extra_strings_keys = array_keys($extra_strings);
		foreach ($extra_strings_keys as $string) {
			if (isset($base_strings[$string]) AND $base_strings[$string] != '') {
				$extra_strings[$string] = $base_strings[$string];
			}
		}

		Util::po_save($base, $extra_strings);
	}

	/**
	 * Get all strings from a template
	 *
	 * @access public
	 * @param string $html
	 * @return array $strings
	 */
	public static function get_strings_from_template($template) {
		if (preg_match_all("/\{t\}(.*?)\{\/t\}/", $template, $strings) == false) {
			return array();
		} else {
			return $strings[1];
		}
	}
}
