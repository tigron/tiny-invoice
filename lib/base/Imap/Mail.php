<?php
/**
 * Imap_Mail class
 *
 * This class handles the IMAP mails
 *
 * @package Tigron Controller
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 */

class Imap_Mail {

	/**
	 * The UID of the mail
	 *
	 * @var int $uid
	 * @access private
	 */
	private $uid;

	/**
	 * Subject
	 *
	 * @var string subject
	 * @access public
	 */
	public $subject = '';

	/**
	 * Body
	 *
	 * @var string $body
	 * @access public
	 */
	public $body = '';

	/**
	 * Attachments
	 *
	 * @var array $attachments
	 * @access public
	 */
	public $attachments = array();

	/**
	 * Structure
	 *
	 * @access public
	 * @param array $structure
	 */
	public $structure;

	/**
	 * Contructor
	 *
	 * Initialize an IMAP connection
	 *
	 * @access public
	 * @param string $hostname
	 * @param string $login
	 * @param string $password
	 */
	public function __construct($uid = null) {
		if ($uid === null) {
			throw new Exception('UID must be set');
		}
		$this->uid = $uid;
	}

	public function get_uid() {
		return $this->uid;
	}

}
