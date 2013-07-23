<?php
/**
 * Util class
 *
 * Contains general purpose utilities
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Util {

	/**
	 * Random code generator
	 *
	 * @access public
	 * @param string $length
	 * @param string $chars
	 * @return string $code
	 */
	public static function create_random_code($length, $chars = '23456789ABCDEFGHKMNPQRSTWXYZ') {
		$code = '';

		for ($i = 1; $i <= $length; $i++) {
			$num = mt_rand(1, strlen($chars));
	        $tmp = substr($chars, $num, 1);
	        $code = $code . $tmp;
		}

		return $code;
	}

	/**
	 * Get table fields
	 *
	 * @access public
	 * @param string $table
	 * @param Database $db
	 * @return array $fields
	 */
	public static function get_table_columns($table, $db = null) {
		if ($db === null) {
			$db = Database::Get();
		}
		$fields = $db->get_columns(strtolower($table));
		return $fields;
	}

	/**
	 * Get table definition
	 *
	 * @access public
	 * @param string $table
	 * @param Database $db
	 * @return array $definition
	 */
	public static function get_table_definition($table, $db = null) {
		if ($db === null) {
			$db = Datbase::Get();
		}
		return $db->get_table_definition(strtolower($table));
	}

	/**
	 * Filter fields to insert/update table
	 *
	 * @access public
	 * @param string $table
	 * @param array $data
	 * @param Database $db
	 * @return $filtered_data
	 */
	public static function filter_table_data($table, $data, $db = null) {
		$table_fields = Util::get_table_columns($table, $db);
		$result = array();
		foreach ($table_fields as $field) {
			if (isset($data[$field])) {
				$result[$field] = $data[$field];
			}
		}

		return $result;
	}

	/**
	 * Fetches the mime type for a certain file
	 *
	 * @param string $file The path to the file
	 * @return string $mime_type
	 */
    public static function mime_type($file)  {
		$handle = finfo_open(FILEINFO_MIME);
		$mime_type = finfo_file($handle,$file);

		if (strpos($mime_type, ';')) {
			$mime_type = preg_replace('/;.*/', ' ', $mime_type);
		}

		return trim($mime_type);
    }

	/**
	 * Sanitize filenames
	 *
	 * @access public
	 * @param string $name
	 * @return string $name
	 */
	public static function sanitize_filename($name) {
		$special_chars = array ('#','$','%','^','&','*','!','~','‘','"','’','\'','=','?','/','[',']','(',')','|','<','>',';','\\',',');
		$name = preg_replace('/^[.]*/','',$name); // remove leading dots
		$name = preg_replace('/[.]*$/','',$name); // remove trailing dots
		$name = str_replace($special_chars, '', $name);// remove special characters
		$name = str_replace(' ','_',$name); // replace spaces with _

		$name_array = explode('.', $name);

		if (count($name_array) > 1) {
			$extension = array_pop($name_array);
		} else {
			$extension = null;
		}

		$name = implode('.', $name_array);
		$name = substr($name, 0, 50);

		if ($extension != null) {
			$name = $name . '.' . $extension;
		}

		return $name;
	}

	/**
	 * Sanitize strings to ascii-only URL safe strings
	 *
	 * @access public
	 * @param string $string The string to sanitize
	 * @return string
	 */
	public static function sanitize_url($string) {
		$string = strtolower($string);
		$string = self::sanitize_filename($string);

		$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
		$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
		$string = str_replace($search, $replace, $string);

		$string = preg_replace('/[^(\x20-\x7F)]*/','', $string);
	    $string = str_replace('_', '', $string);
	    $string = str_replace('-', '', $string);
	    $string = str_replace('.', '', $string);

		return $string;
	}

	/**
	 * Call
	 *
	 * @access public
	 * @param string $method
	 * @param array $arguments
	 */
	public static function __callstatic($method, $arguments) {
		list($classname, $method) = explode('_', $method, 2);
		$class = ucfirst($classname) . '.php';
		require_once LIB_PATH . '/base/Util/' . $class;
		$classname = 'Util_' . $classname;

		if (!method_exists($classname, $method)) {
			throw new Exception('method ' . $method . ' does not exists');
		}

		$result = forward_static_call_array(array($classname, $method), $arguments);
		return $result;
	}
}
