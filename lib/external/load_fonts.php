#!/usr/bin/php
<?php

/**
 * This script will load all necessary fonts into DomPDF
 * Please execute this script on every release
 */
include '../../config/global.php';
require_once PACKAGE_PATH . '/vendor/autoload.php';
require_once EXT_PATH . '/dompdf.config.php';
require_once EXT_PATH . '/packages/vendor/dompdf/dompdf/dompdf_config.inc.php';

/**
 * We have to pretend that the script is executed from command line
 */
$_SERVER["argv"][1] = "system_fonts";
include EXT_PATH . '/packages/vendor/dompdf/dompdf/load_font.php';


install_font('Calibri', 'calibri.ttf', 'calibri_bold.ttf', 'calibri_italic.ttf');
install_font('Verdana', 'verdana.ttf', 'verdana_bold.ttf', 'verdana_italic.ttf', 'verdana_bold_italic.ttf');


function install_font($name, $normal = null, $bold = null, $italic = null, $bold_italic = null) {
	if ($normal !== null) {
		$normal = STORE_PATH . '/pdf/media/font/source/' . $normal;
	}

	if ($bold !== null) {
		$bold = STORE_PATH . '/pdf/media/font/source/' . $bold;
	}

	if ($italic !== null) {
		$italic = STORE_PATH . '/pdf/media/font/source/' . $italic;
	}

	if ($bold_italic !== null) {
		$bold_italic = STORE_PATH . '/pdf/media/font/source/' . $bold_italic;
	}
	install_font_family($name, $normal, $bold, $italic, $bold_italic);
}

