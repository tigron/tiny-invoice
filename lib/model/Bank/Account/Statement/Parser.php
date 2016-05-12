<?php
/**
 * Bank_Account_Statement_Transaction class
 *
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

abstract class Bank_Account_Statement_Parser {

	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Detect
	 *
	 * @access public
	 * @param \Skeleton\File\File $file
	 * @return bool $valid
	 */
	abstract public function detect(\Skeleton\File\File $file);

	/**
	 * Parse
	 *
	 * @access public
	 * @param \Skeleton\File\File $file
	 */
	abstract public function parse(\Skeleton\File\File $file);

	/**
	 * Get by id
	 *
	 * @access public
	 * @param int $id
	 * @return Bank_Account_Statement_Parser $parser
	 */
	public static function get_by_id($id) {
		$db = Database::get();
		$classname = $db->get_one('SELECT classname FROM bank_account_statement_parser WHERE id=?', [ $id ]);
		return new $classname($id);
	}

}
