<?php
/**
 * Error handler
 *
 * Handles errors, mails them to whoever.
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

function error_handler ($errno, $errstr, $errfile = '', $errline = '', $errcontext = array()) {
	// Suppress warnings already supressed by @<function>();
	if (error_reporting() == 0) {
		return;
	}

	static $exec_id = 0;

	if ($exec_id == 0) {
		$exec_id = rand();
	}

	$msg = '';
	$die = false;

	$date = date('Y-m-d H:i:s (T)');
	$errortype = array (E_ERROR             => 'Error',
	                    E_WARNING           => 'Warning',
	                    E_PARSE             => 'Parsing Error',
	                    E_NOTICE            => 'Notice',
	                    E_CORE_ERROR        => 'Core Error',
	                    E_CORE_WARNING      => 'Core Warning',
	                    E_COMPILE_ERROR     => 'Compile Error',
	                    E_COMPILE_WARNING   => 'Compile Warning',
	                    E_USER_ERROR        => 'User Error',
	                    E_USER_WARNING      => 'User Warning',
	                    E_USER_NOTICE       => 'User Notice',
	                    E_STRICT            => 'Runtime Notice',
	                    E_DEPRECATED        => 'Deprecated'
	             );

	switch ($errno) {
		case E_ERROR:
		case E_CORE_ERROR:
		case E_USER_ERROR:
			$die = true;
			break;
		case E_STRICT:
			// Don't report strict errors for PEAR
			if (preg_match('/^\/usr\/share\/php\/(.*)$/', $errfile, $matches))
				return;
		case E_PARSE:
			var_dump($errcontext);
			break;
		case E_NOTICE:
			// Don't report notices errors for PEAR
			if (preg_match('/^\/usr\/share\/php\/(.*)$/', $errfile, $matches))
				return;
		case E_DEPRECATED:
			if (preg_match('/^\/usr\/share\/php\/(.*)$/', $errfile, $matches)) {
				return;
			}
	}

	ob_start();
		print_r($errcontext);
		$vars = ob_get_contents();
	ob_end_clean();

	if (isset($_SERVER['SERVER_NAME'])) {
		$host = $_SERVER['SERVER_NAME'];
	} else {
		$host = 'commandline';
	}

	$subject = $errortype[$errno].' on '.$host;

	$message = 'Date: ' . $date . "\n"
	         . 'Host: ' . $host . "\n"
	         . 'Error: ' . $errno . "\n"
	         . 'Error Type: ' . $errortype[$errno] . "\n"
	         . 'Error Message: ' . $errstr . "\n"
	         . 'Script: ' . $errfile . "\n"
	         . 'Line: ' . $errline . "\n\n"
	         . 'Vartrace: ' . "\n"
	         . $vars;

	report($subject, $message, $die);
}

function exception($exception) {
	if (get_class($exception) == 'Twig_Error_Syntax') {
		twig_exception_syntax($exception);
		return;
	} elseif (get_class($exception) == 'Twig_Error_Loader' OR get_class($exception) == 'Twig_Error_Runtime') {
		twig_exception($exception);
		return;
	}

	ob_start();
		print_r($exception);
		$exception = ob_get_contents();
	ob_end_clean();

	if (isset($_SERVER['SERVER_NAME'])) {
		$host = $_SERVER['SERVER_NAME'];
	} else {
		$host = 'commandline';
	}

	$subject = 'System Exception on '. $host;

	report($subject, $exception, true);
}

function twig_exception($exception) {
	report('Twig error', '<b>' . $exception->getMessage() . '</b> in ' . $exception->getFile(), false, false);
}

function twig_exception_syntax($exception) {
	if (substr($exception->getFileName(), -5) == 'macro') {
		$file = file(APP_PATH . '/macro/' . $exception->getFileName());
	} else {
		$file = file(APP_PATH . '/template/' . $exception->getFileName());
	}

	$line = (int)preg_replace('@.* at line @', '', $exception->getMessage());

	$message = '<b>Error: ' . $exception->getMessage() . "</b>\n\n";

	for ($i = 1; $i<=9; $i++) {
		$display_line = $line-5+$i;

		if ($display_line <= 0 || $display_line > count($file)) {
			continue;
		}

		if ($display_line == $line) {
			$message .= '<span style="background: #eee;">';
		}

		$message .= trim('<b>' . ($display_line) . '</b> ' . htmlspecialchars($file[$line-6+$i]));

		if (($display_line) == $line) {
			$message .= '</span>';
		}

		$message .=  "\n";
	}

	$message .=  "\n";

	// Since we know that the error occurred in a template, it is not safe
	// to assume the Templates are working and callable. Use try/catch.
	try {
		$template = Web_Template::get();

		$message .= '<b>Variables</b> ' . "\n\n";
		$message .= print_r($template->get_assigned(), true);
	} catch (Exception $e) {}

	report('Twig syntax error', $message, false, false);
}

function report($subject, $message, $fatal = false, $backtrace = true) {
	$html =
	'<html>' .
	'   <head>' .
	'       <title>' . $subject . '</title>' .
	'       <style type="text/css">' .
	'           body { font-family: sans-serif; background: #eee; } ' .
	'           pre { border: 1px solid #1b2582; background: #ccc; padding: 5px; }' .
	'           h1 { width: 100%; background: #183452; font-weight: bold; color: #fff; padding: 2px; font-size: 16px;} ' .
	'           h2 { font-size: 15px; } ' .
	'       </style>' .
	'   </head>' .
	'   <body>' .
	'   <h1>' . $subject . '</h1>';

	$html .= '<h2>Message</h2> <pre>' . $message . '</pre>';


	if ($backtrace == true) {
		ob_start();
			debug_print_backtrace();
			$backtrace = ob_get_contents();
		ob_end_clean();

		$html .= '<h2>Backtrace</h2> <pre>' . $backtrace . '</pre>';
	}

	$vartrace = array('_GET'      => isset($_GET) ? $_GET : null,
	                  '_POST'     => isset($_POST) ? $_POST : null,
	                  '_COOKIE'   => isset($_COOKIE) ? $_COOKIE : null,
	                  '_SESSION'  => isset($_SESSION) ? $_SESSION : null,
	                  '_SERVER'   => isset($_SERVER) ? $_SERVER : null
	            );

	$html .= '<h2>Vartrace</h2> <pre> ' . print_r($vartrace, true) . '</pre>';

	$html .=
	'   </body>' .
	'</html>';

	$config = Config::get();

	$headers = 'From: ' . $config->errors_from . "\r\n";
	$headers.= 'Content-Type: text/html; charset=ISO-8859-1 MIME-Version: 1.0';
	mail($config->errors_to, $subject, $html, $headers, '-f ' . $config->errors_from);

	if ($config->debug) {
		echo $html;
	} elseif ($fatal) {
		show_clean_error();
	}

	if ($fatal) {
		exit(1);
	}
}

function show_clean_error() {
	if (file_exists(strtolower(MODULE_PATH . '/error.php'))) {
		require_once(MODULE_PATH . '/error.php');
		$classname = 'Module_Error';
		$module = new $classname();
		$module->accept_request();
	} else {
		echo 'An unexpected error occured. Please try again later.<br />';
	}
}

function pear_error_handler ($error) {
	trigger_error($error->getMessage(), $error->level);
}

set_error_handler('error_handler');
set_exception_handler('exception');

$config = Config::get();
if ($config->debug) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	ini_set('error_reporting', E_ALL | E_STRICT);
} else {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	ini_set('error_reporting', E_ALL & ~E_DEPRECATED);
}