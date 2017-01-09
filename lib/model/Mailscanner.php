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
		$archive = false;
		try {
			$archive = Setting::get_by_name('mailscanner_archive')->value;
		} catch (Exception $e) {}

		$mails = $this->get_mails();

		foreach ($mails as $mail) {
			try {
				$this->process_mail($mail);
				if ($archive) {
					$this->imap->move_mail('processed', $mail);
				}
			} catch (Exception $e) {
				if ($archive) {
					$this->imap->move_mail('unprocessed', $mail);
				}
			}
		}

		if (!$archive) {
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
		if (count($mail->attachments) == 0) {
			throw new Exception('No attachments for this mail');
		}

		foreach ($mail->attachments as $attachment) {

			if (!$attachment['is_attachment']) {
				continue;
			}
			if ($attachment['filename'] == '') {
				$attachment['filename'] = 'document.pdf';
			}
			$file = \Skeleton\File\File::store($attachment['filename'], $attachment['attachment']);

			if (!$file->is_pdf()) {
				$file->delete();
				continue;
			}

			$incoming = new Incoming();
			$incoming->subject = $mail->subject;
			$incoming->file_id = $file->id;
			$incoming->save();

			$pages = [];
			try {
				$pages = @$file->extract_pages();
			} catch (\Exception $e) {
				$pages = [];
			}

			foreach ($pages as $page) {
				$incoming_page = new Incoming_Page();
				$incoming_page->incoming_id = $incoming->id;
				$incoming_page->file_id = $page->id;
				$incoming_page->save();
				$incoming_page->create_preview();
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
