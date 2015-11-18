<?php
/**
 * Transaction_Cleanup_File
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Transaction_Cleanup_File extends Transaction {

	/**
	 * Run
	 *
	 * @access public
	 */
	public function run() {
		$files = File::get_expired();

		foreach ($files as $file) {
			$file->delete();
		}

		echo 'cleanup ' . count($files) . ' file(s)';
		$this->schedule('30 minutes');
	}
}
