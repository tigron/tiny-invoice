<?php
/**
 * File class
 *
<<<<<<< HEAD
=======
 * @author Christophe Gosiau <christophe@tigron.be>
>>>>>>> origin/master
 * @author David Vandemaele <david@tigron.be>
 */

class File extends \Skeleton\File\File {

<<<<<<< HEAD
	/**
	 * is pdf
=======
	function __construct($id = null) {
		parent::__construct($id);
	}

	/**
	 * is PDF?
>>>>>>> origin/master
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
<<<<<<< HEAD

}
=======
}
>>>>>>> origin/master
