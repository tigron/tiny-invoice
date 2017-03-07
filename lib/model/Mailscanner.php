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

use Ddeboer\Imap\Server;

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
		$server = new Server($url, 993, '/imap/ssl/novalidate-cert');
		$this->imap = $server->authenticate($username, $password);

	}

	/**
	 * Do a run
	 *
	 * @access public
	 */
	public function run() {
		$mailboxes = $this->imap->getMailboxes();
		foreach ($mailboxes as $mailbox) {
			if ($mailbox->getName() == 'INBOX') {
				$inbox = $this->imap->getMailbox('INBOX');
			}
		}


		$archive = false;
		try {
			$archive = Setting::get_by_name('mailscanner_archive')->value;
		} catch (Exception $e) {}


		if ($archive) {
			if (!$this->imap->hasMailbox('processed')) {
				$this->imap->createMailbox('processed');
			}
			if (!$this->imap->hasMailbox('unprocessed')) {
				$this->imap->createMailbox('unprocessed');
			}

			$processed_mailbox = $this->imap->getMailbox('processed');
			$unprocessed_mailbox = $this->imap->getMailbox('unprocessed');
		}

		$mails = $inbox->getMessages();



		foreach ($mails as $mail) {
			try {
				$this->process_mail($mail);
				if ($archive) {
					$mail->move($processed_mailbox);
				}
			} catch (Exception $e) {
				if ($archive) {
					$mail->move_mail($unprocessed_mailbox);
				}
			}

			if (!$archive) {
				$mail->delete();
			}
		}
		$inbox->expunge();
	}

	/**
	 * Fetch an email from IMAP and add the attachments as document
	 *
	 * @access private
	 * @param Imap_Mail $mail
	 */
	private function process_mail(Ddeboer\Imap\Message $mail) {
		$attachments = $mail->getAttachments();
		if (count($attachments) == 0) {
			throw new Exception('No attachments for this mail');
		}


		foreach ($attachments as $attachment) {
			$file = \Skeleton\File\File::store($attachment->getFilename(), $attachment->getDecodedContent());

			if (!$file->is_pdf()) {
				$file->delete();
				continue;
			}

			$incoming = new Incoming();
			$incoming->subject = $mail->getSubject();
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
}
