<?php
/**
 * File class
 *
 * @author David Vandemaele <david@tigron.be>
 */

class File extends \Skeleton\File\File {

	/**
	 * is pdf
	 *
	 * @access public
	 * @return bool $is_pdf
	 */
	public function is_pdf() {
		if ($this->mime_type == 'application/pdf') {
			return true;
		} else {
			return false;
		}
	}

}