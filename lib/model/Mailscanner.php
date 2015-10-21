<?php
/**
 * Mailscanner class
 *
 * This class scans the mailbox for incoming documents
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Mailscanner {

	/**
	 * The IMAP connection
	 *
	 * @access private
	 * @var Imap $imap
	 */
	private $imap;

	/**
	 * Constructs a mailscanner, opens the connection.
	 *
	 * @access public
	 * @param string $url
	 * @param string $username
	 * @param string $password
	 */
	public function __construct ($url, $username, $password) {
		$this->imap = new Imap($url, $username, $password);
	}

	/**
	 * Do a run
	 *
	 * @access public
	 */
	public function run() {
		$mails = $this->get_mails();

		foreach ($mails as $mail) {
			$this->process_mail($mail);
			$this->imap->delete_mail($mail);
		}

		$this->imap->close();
	}

	/**
	 * Fetch an email from IMAP and add the attachments as document
	 *
	 * @access private
	 * @param Imap_Mail $mail
	 */
	private function process_mail($mail) {
		try {
			$tag_id = Setting::get_by_name('mailscanner_tag_id')->value;
			if ($tag_id == -1) {
				throw new Exception('No tag set');
			}
			$tag = Tag::get_by_id($tag_id);
		} catch (Exception $e) {
			$tag = null;
		}

		foreach ($mail->attachments as $attachment) {

			if (!$attachment['is_attachment']) {
				continue;
			}

			$file = \Skeleton\File\File::store($attachment['filename'], $attachment['attachment']);

			$document = new Document();
			$document->file_id = $file->id;
			$document->date = date('Y-m-d');
			$document->description = $attachment['filename'];
			$document->title = $mail->subject;
			$document->save();

			if ($tag !== null) {
				$document_tag = new Document_Tag();
				$document_tag->document_id = $document->id;
				$document_tag->tag_id = $tag->id;
				$document_tag->save();
			}
		}
	}

	/**
	 * Get all mails from mailbox
	 *
	 * @access private
	 * @return array $mails
	 */
	private function get_mails() {
		$mails = [];
		while (($mail = $this->imap->fetchmail()) !== false) {
			$mails[] = $mail;
		}
		return $mails;
	}

}
