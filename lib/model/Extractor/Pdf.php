<?php
/**
 * Extractor class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Extractor_Pdf {
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Pager\Page;
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Delete;

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$db = Database::get();
		$db->query('DELETE FROM extractor_pdf WHERE id=?', [ $this->id ]);
		foreach ($this->get_extractor_pdf_fingerprints() as $fingerprint) {
			$fingerprint->delete();
		}
	}

	/**
	 * extract_content
	 *
	 * @access public
	 * @param Document $document (optional)
	 * @return string $content
	 */
	public function extract_content(Document $document = null) {
		// load the document
		if ($document === null) {
			$document = SetaPDF_Core_Document::loadByFilename($this->document->file->get_path());
		} else {
			$document = SetaPDF_Core_Document::loadByFilename($document->file->get_path());
		}

		// get access to its pages
		$pages = $document->getCatalog()->getPages();

		$extractor = new SetaPDF_Extractor($document);

		$result = '';
		for ($i=1; $i<= $pages->count(); $i++) {
			$result .= $extractor->getResultByPageNumber($i);
		}
		return $result;
	}

	/**
	 * Parse content
	 *
	 * @access public
	 * @param Document $document (optional)
	 */
	public function extract_data(Document $document = null) {
		if ($document === null) {
			$content = $this->extract_content();
		} else {
			$content = $this->extract_content($document);
		}
		$data = [];

		ob_start();
		set_error_handler(null);
		$return = eval($this->eval);
		$error = error_get_last();
		restore_error_handler();
		$output = ob_get_contents();
		ob_end_clean();

		if ( $return === false && $error ) {
			$exception = new Extractor_Eval_Exception();
			$exception->line = $error['line'];
			$exception->setMessage($error['message']);
			throw $exception;
		}

		$return = [
			'data' => $data,
			'output' => $output
		];

		return $return;
	}

	/**
	 * Match
	 * Check if a given document matches the extractor fingerprints
	 *
	 * @access public
	 * @param Document $document
	 */
	public function match(Document $document) {
		// load the document
		$seta_document = SetaPDF_Core_Document::loadByFilename($document->file->get_path());
		$preview_width = $document->get_preview()->width;
		$preview_height = $document->get_preview()->height;

		// get access to its pages
		$pages = $seta_document->getCatalog()->getPages();
		$first_page = $pages->getPage(1);

		$page_width = $first_page->getWidth();
		$page_height = $first_page->getHeight();

		$width_factor = $page_width / $preview_width;
		$height_factor = $page_height / $preview_height;

		// the interresting part: initiate an extractor instance
		$extractor = new SetaPDF_Extractor($seta_document);

		foreach ($this->get_extractor_pdf_fingerprints() as $extractor_pdf_fingerprint) {
			$x1 = $extractor_pdf_fingerprint->x * $width_factor;
			$x2 = ($extractor_pdf_fingerprint->x+$extractor_pdf_fingerprint->width) * $width_factor;
			$y1 = ($preview_height-$extractor_pdf_fingerprint->y) * $height_factor;
			$y2 = ($preview_height - ($extractor_pdf_fingerprint->y+$extractor_pdf_fingerprint->height)) * $height_factor;

			// create a word strategy instance
			$strategy = new SetaPDF_Extractor_Strategy_Word();

			// pass a rectangle filter to the strategy
			$strategy->setFilter(new SetaPDF_Extractor_Filter_Rectangle(
				new SetaPDF_Core_Geometry_Rectangle($x1, $y1, $x2, $y2),
				SetaPDF_Extractor_Filter_Rectangle::MODE_CONTACT
			));
			$extractor->setStrategy($strategy);

			// get the result which is only the sender name and address in the address field
			$result = $extractor->getResultByPageNumber(1);

			$content = '';
			foreach ($result as $word) {
				$content .= $word;
			}

			if ($content != $extractor_pdf_fingerprint->value) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Get extractor_pdf_fingerprints
	 *
	 * @access public
	 * @return array $extractor_pdf_fingerprints
	 */
	public function get_extractor_pdf_fingerprints() {
		return Extractor_Pdf_Fingerprint::get_by_extractor_pdf($this);
	}

}
