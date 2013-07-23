<?php
/**
 * Email class
 *
 * Send emails
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

require_once LIB_PATH . '/base/Email/Template.php';

class Email {
	/**
	 * Email type
	 *
	 * @access private
	 * @var string $type
	 */
	private $type = '';

	/**
	 * Sender
	 *
	 * @access private
	 * @var array $sender
	 */
	private $sender = null;

	/**
	 * Recipients
	 *
	 * @access private
	 * @var array $recipients
	 */
	private $recipients = array();

	/**
	 * Assigned variables
	 *
	 * @access private
	 * @var array $assigns
	 */
	private $assigns = array();

	/**
	 * Files
	 *
	 * @access private
	 * @var array $files
	 */
	private $files = array();

	/**
	 * Manual files
	 *
	 * @access private
	 * @var array $manual_files
	 */
	private $manual_files = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $type
	 */
	public function __construct($type) {
		if ($type === null) {
			throw new Exception('No email type specified');
		}
		$this->type = $type;
	}

	/**
	 * Add recipient
	 *
	 * The passed object must contain the following properties:
	 * - firstname
	 * - lastname
	 * - email
	 *
	 * Optionally, it can contain language as well, if not, the default language is used.
	 *
	 * @access public
	 * @param mixed $recipient
	 * @param string $type Recipient type, defaults to 'to'
	 */
	public function add_recipient($recipient, $type = 'to') {
		$config = Config::get();

		try {
			$language = $recipient->language;
		} catch (Exception $e) {
			$language = Language::get_by_name_short($config->default_language);
		}

		$this->add_recipient_address($recipient->firstname . ' ' . $recipient->lastname, $recipient->email, $language, $type);
	}

	/**
	 * Add recipient_address
	 *
	 * @access private
	 * @param string $name
	 * @param string $email
	 * @param Language $language
	 * @param string $type Recipient type, defaults to 'to'
	 */
	public function add_recipient_address($name = '', $email, Language $language = null, $type = 'to') {
		if ($language === null) {
			$language = Language::get_by_id(1);
		}

		$this->recipients[$type][] = array(
			'name' => $name,
			'email' => $email,
			'language' => $language
		);
	}

	/**
	 * Add a file
	 *
	 * @access public
	 * @param File $file
	 */
	public function add_file($file) {
		if (is_a($file, 'File')) {
			$this->files[] = $file;
		} else {
			$this->manual_files[] = $file;
		}
	}

	/**
	 * Set sender
	 *
	 * @param string $email
	 * @param string $address
	 */
	public function set_sender($email, $name = null) {
		$this->sender = array(
			'name' => $name,
			'email' => $email,
		);
	}

	/**
	 * Assign
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public function assign($key, $value) {
		$this->assigns[$key] = $value;
	}

	/**
	 * Send email
	 *
	 * @access public
	 */
	public function send() {
		if (!$this->validate($errors)) {
			throw new Exception('Cannot send email, Mail not validated. Errored fields: ' . implode(', ', $errors));
		}

		$language = $this->recipients['to'][0]['language'];
		$template = new Email_Template($this->type, $language);

		foreach ($this->assigns as $key => $value) {
			$template->assign($key, $value);
		}

		$config = Config::Get();
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		$message = Swift_Message::newInstance()
			->setBody($template->render('html'), 'text/html')
			->addPart($template->render('text'), 'text/plain')
			->setSubject($template->render('subject'))
		;

		if (isset($this->sender['name'])) {
			$message->setFrom(array($this->sender['email'] => $this->sender['name']));
		} else {
			$message->setFrom($this->sender['email']);
		}

		if (isset($config->archive_mailbox) AND $config->archive_mailbox != '') {
			$message->addBcc($config->archive_mailbox);
		}

		$headers = $message->getHeaders();
		$headers->addTextHeader('X-MailType', $config->company_info['identifier'] . '_' . $this->type);

		$this->add_html_images($message);
		$this->attach_files($message);

		foreach ($this->recipients as $type => $recipients) {
			foreach ($recipients as $recipient) {
				if ($recipient['name'] != '') {
					$addresses[$recipient['email']] = $recipient['name'];
				} else {
					$addresses[] = $recipient['email'];
				}
			}

			$set_to = 'set' . ucfirst($type);
			call_user_func(array($message, $set_to), $addresses);
		}

		$mailer->send($message);
		unset($template);
	}

	/**
	 * Validate
	 *
	 * @access private
	 * @return bool $validated
	 * @param array $errors
	 */
	public function validate(&$errors = array()) {
		if (!isset($this->type)) {
			$errors[] = 'type';
		}

		if (!isset($this->sender['email'])) {
			$errors[] = 'sender[email]';
		}

		if (!isset($this->recipients) or count($this->recipients) == 0) {
			$errors[] = 'recipients';
		}

		if (count($errors) == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add embedded HTML images (image dir)
	 *
	 * @access private
	 * @param Swift_Message $message
	 */
	private function add_html_images(&$message) {
		$path = STORE_PATH . '/email/media/';

		$html_body = $message->getBody();

		if ($handle = opendir($path)) {
			while (false !== ($file = readdir($handle))) {
				if (substr($file,0,1) != '.' && strpos($html_body, $file) !== false) {
					$swift_image = Swift_Image::newInstance(file_get_contents($path . $file), $file, Util::mime_type($path . $file));
					$html_body = str_replace($file, $message->embed($swift_image), $html_body);
				}
			}
		}

		$message->setBody($html_body);

		closedir($handle);
	}

	/**
	 * Attach files
	 *
	 * @access private
	 * @param Swift_Message $message
	 */
	private function attach_files(&$message) {
		foreach ($this->files as $file) {
			$message->attach(Swift_Attachment::fromPath($file->get_path())->setFilename($file->name));
		}

		foreach ($this->manual_files as $file) {
			$message->attach(Swift_Attachment::fromPath($file));
		}
	}
}
