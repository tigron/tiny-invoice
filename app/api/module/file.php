<?php
/**
 * Module File
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_File extends \Skeleton\Package\Api\Web\Module\Call {

	/**
	 * Get by id
	 *
	 * Get a user by his ID
	 *
	 * @access public
	 * @param int $id
	 * @return array $user
	 */
	public function call_getById() {
		$file = File::get_by_id($_REQUEST['id']);
		return $file->get_info();
	}
}
