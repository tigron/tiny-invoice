<?php
/**
 * This script will generate po files based on all strings
 * that needs to be translated from the templates
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once dirname(__FILE__) . '/../../config/global.php';

require_once EXT_PATH . '/twig/lib/Twig/Autoloader.php';
require_once LIB_PATH . '/model/Language.php';
require_once LIB_PATH . '/base/Util.php';
require_once LIB_PATH . '/base/Translation.php';
require_once LIB_PATH . '/base/Web/Template/Twig/Extension/Default.php';

$template_directories = array('template', 'macro');

$config = Config::Get();
Twig_Autoloader::register();

$applications_array = $config->applications;
$applications = array();
foreach ($applications_array as $application) {
	if (is_array($application)) {
		$applications[] = $application['name'];
	} else {
		$applications[] = $application;
	}
}

$applications[] = 'email';
$applications[] = 'pdf';

/**
 * First: generate the full template cache
 */
foreach ($applications as $application) {
	foreach ($template_directories as $template_directory) {
		if ($application == 'email') {
			$directory = STORE_PATH . '/email/template';
		} elseif ($application == 'pdf') {
			$directory = STORE_PATH . '/pdf/template';
		} else {
			$directory = ROOT_PATH . '/app/' . $application . '/template';
		}

		if (file_exists($directory)) {
			$directories[] = $directory;
		}
	}

	$loader = new Twig_Loader_Filesystem($directories);

	// force auto-reload to always have the latest version of the template
	$twig = new Twig_Environment(
		$loader, array(
			'cache' => TMP_PATH . '/twig/' . $application,
			'auto_reload' => true
		)
	);

	$twig->addExtension(new Twig_Extensions_Extension_Default());
	$twig->addExtension(
		new Twig_Extensions_Extension_I18n(
			array(
				'function_translation' => 'Translation::translate',
				'function_translation_plural' => 'Translation::translate_plural',
			)
		)
	);

	try {
		$twig->addGlobal('base', $twig->loadTemplate('base.macro'));
	} catch (Twig_Error_Loader $e) { /* base.macro not found, #care */ }

	// iterate over all the templates
	foreach ($directories as $directory) {
		foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
			$file = str_replace($directory.'/', '', $file);

			if (strrpos($file, '.') ==  (strlen($file)-1)) {
				continue;
			}

			$template = $twig->loadTemplate($file);
		}
	}
}

/**
 * Second: make a copy of every po file
 */
foreach ($applications as $application) {
	$languages = Language::get_all();

	foreach ($languages as $language) {
		$config = Config::Get();
		if ($config->base_language == $language->name_short) {
			continue;
		}
		if (file_exists(PO_PATH . '/' . $language->name_short . '/' . $application . '.po')) {
			rename(PO_PATH . '/' . $language->name_short . '/' . $application . '.po', PO_PATH . '/' . $language->name_short . '/' . $application . '_def.po');
		}
	}
}

/**
 * Third: translate every application
 */
foreach ($applications as $application) {
	translate_application($application);
}

/**
 * Fourth: do a merge from each reference file
 */
foreach ($applications as $application) {
	$languages = Language::get_all();

	foreach ($languages as $language) {
		$config = Config::Get();
		if ($config->base_language == $language->name_short) {
			continue;
		}

		if (!file_exists(PO_PATH . '/' . $language->name_short . '/' . $application . '_def.po')) {
			continue;
		}

		Util::po_merge(PO_PATH . '/' . $language->name_short . '/' . $application . '_def.po', PO_PATH . '/' . $language->name_short . '/' . $application . '.po');
		rename(PO_PATH . '/' . $language->name_short . '/' . $application . '_def.po', PO_PATH . '/' . $language->name_short . '/' . $application . '.po');
	}
}

/**
 * Function
 *
 * Translate application: take all files from the cache and translate
 * them.
 */
function translate_application($application) {
	if (is_dir(TMP_PATH . '/twig/' . $application)) {
		$files = scandir(TMP_PATH . '/twig/' . $application);

		foreach ($files as $file) {
			if ($file[0] == '.') {
				continue;
			}

			if (is_dir(TMP_PATH . '/twig/' . $application . '/' . $file)) {
				translate_directory(TMP_PATH . '/twig/' . $application . '/' . $file, $application);
				continue;
			}
		}
	}
}

/**
 * Function
 *
 * Translate directory: take all files from a certain directory and translate
 * them.
 */
function translate_directory($directory, $application) {
	$files = scandir($directory);

	foreach ($files as $file) {
		if ($file[0] == '.') {
			continue;
		}

		if (is_dir($directory . '/' . $file)) {
			translate_directory($directory . '/' . $file, $application);
			continue;
		}

		translate_file($directory . '/' . $file, $application);
	}
}

/**
 * Function
 *
 * Translate file: translate a certain file
 */
function translate_file($filename, $application) {
	$languages = Language::get_all();

	foreach ($languages as $language) {
		$config = Config::Get();
		if ($config->base_language == $language->name_short) {
			continue;
		}

		if (!file_exists(PO_PATH . '/' . $language->name_short . '/')) {
			mkdir(PO_PATH . '/' . $language->name_short . '/', 0755, true);
		}

		if (!file_exists(PO_PATH . '/' . $language->name_short . '/' . $application . '.po')) {
			touch(PO_PATH . '/' . $language->name_short . '/' . $application . '.po');
		}

		$content = file($filename);
		$content = substr($content[2], 3, strlen($content[2])-6);
		echo 'translating ' . $filename . ' to ' . $language->name_short . ' for ' . $application . ' containing ' . $content . "\n";

		$content = file_get_contents($filename);
		$content_orig = $content;
		$strings = array();

		while (strpos($content, 'Translation::translate_plural("') !== false) {
			$content = substr($content, strpos($content, 'Translation::translate_plural("') + strlen('Translation::translate_plural("'));
			$string = substr($content, 0, strpos($content, '", "'));
			$strings[] = stripslashes(str_replace('\t', "\t", $string));
			$content = substr($content, strpos($content, '", "') + strlen('", "'));
			$string = substr($content, 0, strpos($content, '", '));
			$strings[] = stripslashes(str_replace('\t', "\t", $string));
			$content = substr($content, strpos($content, '", '));
		}

		$content = $content_orig;

		while (strpos($content, 'Translation::translate("') !== false) {
			$content = substr($content, strpos($content, 'Translation::translate("') + strlen('Translation::translate("'));
			$string = substr($content, 0, strpos($content, '")'));
			$content = substr($content, strpos($content, '")'));
			$strings[] = stripslashes(str_replace('\t', "\t", $string));
		}

		$current_strings = Util::po_load(PO_PATH . '/' . $language->name_short . '/' . $application . '.po');
		$untranslated = array();

		foreach ($strings as $string) {
			$untranslated[$string] = '';
		}

		$strings = array_merge($current_strings, $untranslated);
		asort($strings);

		Util::po_save(PO_PATH . '/' . $language->name_short . '/' . $application . '.po', $strings);
	}
}
?>
