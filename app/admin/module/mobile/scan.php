<?php
/**
 * Module Index
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Session;
use \Skeleton\Core\Web\Module;
use \setasign\Fpdi\TcpdfFpdi;

class Web_Module_Mobile_Scan extends Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = false;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'mobile/scan.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		if (!isset($_COOKIE['mobile-token'])) {
			Session::redirect('/mobile');
		}
		try {
			$mobile = Mobile::get_by_token($_COOKIE['mobile-token']);
		} catch (Exception $e) {
			Session::redirect('/mobile');
		}
	}


	/**
	 * display_upload (AJAX)
	 */
	public function display_upload() {
		$this->template = false;
		$parts = explode(',', $_POST['picture']);
		$base_64 = array_pop($parts);
		$base_64 = base64_decode($base_64);

		$file = File::store('my_filename.png', $base_64);
		$mobile = Mobile::get_by_token($_COOKIE['mobile-token']);

		$incoming = new Incoming();
		$incoming->file_id = $file->id;
		$incoming->subject = 'Uploaded via mobile by ' . $mobile->user->firstname . ' ' . $mobile->user->lastname;
		$incoming->save();

		$pdf = new TcpdfFpdi('P', 'mm', [ 210.00, 297.00 ]);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->SetAutoPageBreak(true, 0);

		// set document information
		$pdf->SetCreator($mobile->user->firstname . ' ' . $mobile->user->lastname);
		$pdf->SetAuthor($mobile->user->firstname . ' ' . $mobile->user->lastname);

		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(0, 0, 0);
		$pdf->SetXY(0, 0);

		// add a page
		$pdf->AddPage();

		// set JPEG quality
		$pdf->setJPEGQuality(75);
		$pdf->Image('@'. $base_64, '0', '0', 210, 297, '', '', '', false, 300, '', false, false, 0, false, false, true);
		$content = $pdf->Output('pdf_output.pdf', 'S');
		$pdf = \Skeleton\File\File::store('pdf_output.pdf', $content);
		$incoming_page = new Incoming_Page();
		$incoming_page->incoming_id = $incoming->id;
		$incoming_page->preview_file_id = $file->id;
		$incoming_page->file_id = $pdf->id;
		$incoming_page->save();
		echo json_encode(['status' => 'success', 'path' => $pdf->get_path()]);
	}


	/**
	 * display_upload_success
	 */
	public function display_upload_success() {
		Session::set_sticky('message', 'upload_success');
		Session::redirect('/mobile');
	}
}
