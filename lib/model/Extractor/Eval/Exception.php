<?php
/**
 * Extractor_Eval_Exception class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
class Extractor_Eval_Exception extends Exception {

	/**
	 * Type
	 *
	 * @var int $type
	 */
	public $type = 0;

	/**
	 * Line
	 *
	 * @var int $line
	 */
	public int $line = 0;

	/**
	 * Set message
	 *
	 * @access public
	 * @param string $message
	 */
	public function setMessage($message) {
		$this->message = $message;
	}
}
