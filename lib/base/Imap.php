<?php
/**
 * Imap class
 *
 * This class handles the IMAP connection
 *
 * @package Tigron Controller
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 */

class Imap {

	/**
	 * The mailbox Connection
	 *
	 * @access private
	 * @var resource mbox
	 */
	private $connection;

	/**
	 * Url
	 *
	 * @access private
	 * @var string $url
	 */
	private $url;

	/**
	 * The number of messages
	 *
	 * @access private
	 * @var int $emails_quan
	 */
	private $message_count = 0;

	/**
	 * The index of the current mail
	 *
	 * @access private
	 * @var int $current_email_index
	 */
	private $current_email_index = 0;

	/**
	 * The index of the current attachment
	 *
	 * @access private
	 * @var int $current_attach_index
	 */
	private $current_attach_index = 1;

	/**
	 * The attachment
	 *
	 * @access private
	 * @var mixed $attachment
	 */
	private $attachment = '';

	/**
	 * The attachment types
	 *
	 * @access private
	 * @var array $attachment_types
	 */
	private $attachment_types = array('ATTACHMENT', 'INLINE');

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
	public function __construct($host, $login, $password) {
		$this->connect($host, $login, $password);
	}

	/**
	 * Connect to the IMAP server
	 *
	 * @access private
	 */
	private function connect($host, $login, $password) {
		$url = '{' . $host . ':143/novalidate-cert}';
		$this->connection = @imap_open($url, $login, $password);
		if ($this->connection === false) {
			throw new Exception('Unable to connect to IMAP server');
		}
		$this->url = $url;
		$this->message_count = imap_num_msg($this->connection);
	}

	/**
	 * Fetch the next mail
	 *
	 * @access public
	 * @return
	 */
	public function fetchmail() {
		if ($this->connection === false) {
			throw new Exception('Not connected to server');
		}
		if ($this->current_email_index == $this->message_count) {
			return false;
		}
		$this->current_email_index++;
		$this->current_attach_index = 1;

		$mail = new Imap_Mail(imap_uid($this->connection, $this->current_email_index));
		$mail->structure = imap_fetchstructure($this->connection, $this->current_email_index);
		$mail->body = imap_fetchbody($this->connection, $this->current_email_index, 1);
		$mail->subject = iconv_mime_decode(imap_headerinfo($this->connection, $this->current_email_index)->subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, "ISO-8859-1");
		$mail->attachments = $this->fetch_attachments($mail->structure);

		return $mail;
	}

	/**
	 * Fetch the attachments for an email
	 *
	 * @access private
	 * @param array $mail_structure
	 * @return array $attachments
	 */
	private function fetch_attachments($structure) {
		$attachments = array();
		if(isset($structure->parts) && count($structure->parts)) {

			for($i = 0; $i < count($structure->parts); $i++) {

				$attachments[$i] = array(
					'is_attachment' => false,
					'filename' => '',
					'name' => '',
					'attachment' => ''
				);

				if($structure->parts[$i]->ifdparameters) {
					foreach($structure->parts[$i]->dparameters as $object) {
						if(strtolower($object->attribute) == 'filename') {
							$attachments[$i]['is_attachment'] = true;
							$attachments[$i]['filename'] = $object->value;
						}
					}
				}

				if($structure->parts[$i]->ifparameters) {
					foreach($structure->parts[$i]->parameters as $object) {
						if(strtolower($object->attribute) == 'name') {
							$attachments[$i]['is_attachment'] = true;
							$attachments[$i]['name'] = $object->value;
						}
					}
				}

				if($attachments[$i]['is_attachment']) {
					$attachments[$i]['attachment'] = imap_fetchbody($this->connection, $this->current_email_index, $i+1);
					if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
						$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
					}
					elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
						$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
					}
				}
			}
		}
		return $attachments;
	}

	/**
	 * Delete the current mail
	 *
	 * @access public
	 * @param Imap_Mail $mail
	 */
	public function delete_mail(Imap_Mail $mail = null) {
		if ($mail === null) {
			if ($this->current_email_index > 0) {
				imap_delete($this->connection, $this->current_email_index);
			}
		} else {
			imap_delete($this->connection, $mail->get_uid(), FT_UID);
		}
		imap_expunge($this->connection);
	}

	/**
	 * Move mail in the mailbox to a folder
	 *
	 * @access private
	 */
	public function move_mail($destination, Imap_Mail $mail = null) {
		$mailboxexist = imap_list($this->connection, $this->url, '*');

		if ($mailboxexist == false) {
			imap_createmailbox($this->connection, $this->url.$destination);
		}
		if ($mail !== null) {
			$message = imap_msgno($this->connection, $mail->get_uid());
		} else {
			$message = $this->current_email_index;
		}
		imap_mail_move($this->connection, $message, $destination);
	}

	/**
	 * Mark the message as unread to draw attention
	 *
	 * @param Mailscanner_Mail $mail
	 * @access private
	 */
	public function mark_unread(Imap_Mail $mail = null) {
		if ($mail !== null) {
			$message = imap_msgno($this->connection, $mail->get_uid());
		} else {
			$message = $this->current_email_index;
		}

		imap_clearflag_full($this->connection, $message, "\\Seen");
		imap_setflag_full($this->connection, $message, "\\Flagged \\Recent");
	}

	/**
	 * Close
	 *
	 * @access public
	 */
	public function close() {
		if ($this->connection) {
			imap_close($this->connection, CL_EXPUNGE);
		}
	}
}
?>
