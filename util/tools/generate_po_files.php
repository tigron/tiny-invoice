<?php
/**
 * This tool generates the .po files for each application.
 * The resulting files can be found in $ROOT/po/
 */

require_once dirname(__FILE__) . '/../../config/global.php';

$config = Config::Get();

// Fetch paths for Applications
$applications = Application::get_all();

$paths = array();
foreach ($applications as $application) {
	$paths[$application->name] = $application->path;
}

// Manually add mails and PDF documents
$paths['email'] = STORE_PATH . '/email/template';
$paths['pdf'] = STORE_PATH . '/pdf/template';

// Translate all the applications
foreach ($paths as $application => $directory) {
	translate_application($application, $directory);
}

/**
 * Translate an application
 *
 * @param string $application Name of the application
 * @param string $directory Application path
 */
function translate_application($application, $directory) {
	$config = Config::Get();

	echo 'translating ' . $application . ' (' . $directory . ')';

	// Fetch the templates in this directory
	$templates = get_templates($directory);
	$strings = array();

	// Parse all the files we found
	foreach ($templates as $template) {
		$strings = array_merge($strings, get_strings($template));
	}

	// Translate the strings
	$languages = Language::get_all();
	foreach ($languages as $language) {
		// Don't create a .po file if it is our base_language
		if ($language->name_short == $config->base_language) {
			continue;
		}

		echo ' ' . $language->name_short;

		// If we already have a (partially) translated file, merge
		if (file_exists(PO_PATH . '/' . $language->name_short . '/' . $application . '.po')) {
			$translated = Util::po_load(PO_PATH . '/' . $language->name_short . '/' . $application . '.po');
			$old_translated = Util::po_load(PO_PATH . '/' . $language->name_short . '.po');
			$translated = array_merge($translated, $old_translated);
		} else {
			$translated = array();
		}

		// Create a new array with the merged translations
		$new_po = array();
		foreach ($strings as $string) {
			if (isset($translated[$string]) and $translated[$string] != '') {
				$new_po[$string] = $translated[$string];
			} else {
				$new_po[$string] = '';
			}
		}

		// And save!
		Util::po_save(PO_PATH . '/' . $language->name_short . '/' . $application . '.po', $application, $language, $new_po);
	}

	echo "\n";
}

/**
 * Parse all translatable stings out of a file
 *
 * @param string $file The full path of the file to parse
 */
function get_strings($file) {
	$content = file_get_contents($file);

	/**
	 * {t}string{/t}
	 */
	preg_match_all("/\{t\}(.*?)\{\/t\}/", $content, $matches);
	$smarty_strings = $matches[1];

	/**
	 * {% trans "string" %}
	 */
	preg_match_all("/\{%(.*?)trans \"(.*?)\"(.*?)%\}/", $content, $matches);
	$twig_strings = $matches[2];

	/**
	 * {% trans 'string' %}
	 */
	preg_match_all("/\{%(.*?)trans '(.*?)'(.*?)%\}/", $content, $matches);
	$twig_strings2 = $matches[2];

	/**
	 * 'string'|trans
	 */
	preg_match_all("/'([^']+)'\|trans/", $content, $matches);
	$twig_strings3 = $matches[1];

	/**
	 * "string"|trans
	 */
	preg_match_all("/\"([^\"]+)\"\|trans/", $content, $matches);
	$twig_strings4 = $matches[1];

	/**
	 * {% trans %}string{% endtrans %}
	 */
	preg_match_all("/\{% trans %\}(.*?)\{% endtrans %\}/s", $content, $matches);
	$twig_strings5 = $matches[1];

	/**
	 * Translation::translate('string')
	 */
	preg_match_all("/Translation\:\:translate\(\"(.*?)\"\)/", $content, $matches);
	$module_strings = $matches[1];

	$strings = array_merge($twig_strings, $twig_strings2, $twig_strings3, $twig_strings4, $twig_strings5, $smarty_strings, $module_strings);
	return $strings;
}

/**
 * Find all template files in a given directory
 *
 * @param string $directory Directory to search for templates
 */
function get_templates($directory) {
	// Get all files
	$files = scandir($directory);

	// Loop over all the files, recurse if it is a directory
	$templates = array();
	foreach ($files as $file) {
		if ($file[0] == '.') {
			continue;
		}

		// If it is a directory, recurse
		if (is_dir($directory . '/' . $file)) {
			$dir_templates = get_templates($directory . '/' . $file);
			foreach ($dir_templates as $dir_template) {
				$templates[] = $dir_template;
			}
			continue;
		}

		// If it is a file that we support, add it to the result
		if (strpos($file, '.') !== false) {
			$file_parts = explode('.', $file);
			$extension = array_pop($file_parts);
			if ($extension == 'twig' OR $extension == 'tpl' OR $extension == 'php') {
				$templates[] = $directory . '/' . $file;
			}
		}
	}

	return $templates;
}
