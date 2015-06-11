<?php
/**
 * Media detection and serving of media files
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Media {
	/**
	 * Image extensions
	 *
	 * @var array $filetypes
	 * @access private
	 */
	private static $filetypes = array(
		'image' => array(
			'gif',
			'jpg',
			'jpeg',
			'png',
			'ico',
		),
		'doc' => array(
			'pdf',
		),
		'css' => array(
			'css',
		),
		'font' => array(
			'woff',
			'ttf',
			'otf',
			'eot'
		),
		'javascript' => array(
			'js',
		),
		'tools' => array(
			'html',
			'htm'
		),
	);

	/**
	 * Detect if the request is a request for media
	 *
	 * @param $request array
	 * @access public
	 */
	public static function detect($request) {
		if (count($request) == 0) {
			return;
		}

		// Find the filename and extension
		$filename = $request[count($request)-1];
		$extension = substr($filename, strrpos($filename, '.'));

		// If the request does not contain an extension, it's not to be handled by media
		if (strpos($extension, '.') !== 0) {
			return;
		}

		// Remove the . from the extension
		$extension = substr($extension, 1);
		$request_string = implode('/', $request);

		// Detect if it is a request for multiple files
		if (strpos($request_string, '&/') !== false) {
			$files = explode('&/', $request_string);

			$mtime = 0;
			foreach ($files as $file) {
				$file_mtime = self::fetch('mtime', $file, $extension);

				if ($file_mtime === false) {
					self::fail($extension);
				}

				if ($file_mtime > $mtime) {
					$mtime = $file_mtime;
				}
			}

			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
				if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == gmdate('D, d M Y H:i:s', $mtime).' GMT') {
					// Cached version
					self::output($extension, '', $mtime);
				}
			}

			$content = '';
			foreach ($files as $file) {
				$content .= self::fetch('content', $file, $extension) . "\n";
			}

			$content = $content;
			$filename = 'compacted.' . $extension;
		} else {
			$mtime = self::fetch('mtime', $request_string, $extension);

			if ($mtime === false) {
				self::fail();
			}

			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
				if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == gmdate('D, d M Y H:i:s', $mtime).' GMT') {
					// Cached version
					self::output($extension, '', $mtime);
				}
			}

			$content = self::fetch('content', $request_string, $extension);
		}

		self::output($extension, $content, $mtime);
	}

	/**
	 * Fail
	 *
	 * @access private
	 */
	private static function fail() {
		header("HTTP/1.1 404 Not Found", true);
		echo '404 File Not Found (media)';
		exit();
	}

	/**
	 * Fetch the contents and mtime of a file
	 *
	 * @access private
	 * @param string $path
	 * @param string $extension
	 */
	private static function fetch($type, $path, $extension) {
		foreach (self::$filetypes as $filetype => $extensions) {
			if (in_array($extension, $extensions)) {
				if (file_exists(Application::Get()->media_path . '/' . $filetype . '/' . $path)) {
					if ($type == 'mtime') {
						return filemtime(Application::Get()->media_path . '/' . $filetype . '/' . $path);
					} else {
						return file_get_contents(Application::Get()->media_path . '/' . $filetype . '/' . $path);
					}
				} else if ((file_exists(Application::Get()->media_path . '/tools/' . $path))) {
					if ($type == 'mtime') {
						return filemtime(Application::Get()->media_path . '/tools/' . $path);
					} else {
						return file_get_contents(Application::Get()->media_path . '/tools/' . $path);
					}
				} else {
					return false;
				}
			}
		}
	}

	/**
	 * Ouput the content of the file and cache it
	 *
	 * @param string $path
	 * @param string $extension
	 * @access private
	 */
	private static function output($extension, $content, $mtime) {
		self::cache($mtime);
		header('Content-Type: ' . self::get_mime_type($extension));
		echo $content;
		exit();
	}

	/**
	 * Get the mime type of a file
	 *
	 * @access private
	 * @param string $filename
	 * @return string $mime_type
	 */
	private static function get_mime_type($extension) {
		$mime_type = '';
		switch ($extension) {
			case 'htm' :
			case 'html': $mime_type = 'text/html';
			             break;

			case 'css' : $mime_type = 'text/css';
			             break;

			case 'ico' : $mime_type = 'image/x-icon';
			             break;

			case 'js'  : $mime_type = 'text/javascript';
			             break;

			case 'png' : $mime_type = 'image/png';
			             break;

			case 'gif' : $mime_type = 'image/gif';
			             break;

			case 'jpg' :
			case 'jpeg': $mime_type = 'image/jpeg';
			             break;
			case 'pdf' : $mime_type = 'application/pdf';
						 break;

			default    : $mime_type = 'application/octet-stream';
		}

		return $mime_type;
	}

	/**
	 * Detect if the file should be resent to the client or if it can use its cache
	 *
	 * @param string filename requested
	 * @access private
	 */
	private static function cache($mtime) {
		$gmt_mtime = gmdate('D, d M Y H:i:s', $mtime).' GMT';

		header('Cache-Control: public');
		header('Pragma: public');

		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime) {
				header('Expires: ');
				header('HTTP/1.1 304 Not Modified');
				exit;
			}
		}

		header('Last-Modified: '. $gmt_mtime);
		header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+30 minutes')).' GMT');
	}
}
