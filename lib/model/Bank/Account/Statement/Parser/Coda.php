<?php
/**
 * Bank_Account_Statement_Transaction class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use Codelicious\Coda\Parser;

class Bank_Account_Statement_Parser_Coda extends Bank_Account_Statement_Parser {

	/**
	 * Detect
	 *
	 * @access public
	 * @param \Skeleton\File\File $file
	 * @return bool $valid
	 */
	public function detect(\Skeleton\File\File $file) {
		$parser = new Parser();
		$statements = $parser->parseFile($file->get_path(), 'simple');
		if ($file->mime_type != 'text/plain' OR count($statements) == 0) {
			return false;
		}
		return true;
	}

	/**
	 * Parse
	 *
	 * @access public
	 * @param \Skeleton\File\File $file
	 */
	public function parse(\Skeleton\File\File $file) {
	}

}
