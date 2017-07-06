<?php
/**
 * Extractor_Pdf_Fingerprint class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Extractor_Pdf_Fingerprint {
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Pager\Page;
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Delete;

	/**
	 * Extract content
	 *
	 * @access public
	 */
	public function extract_content() {
		// load the document
		$document = SetaPDF_Core_Document::loadByFilename($this->extractor_pdf->document->file->get_path());

		// get access to its pages
		$pages = $document->getCatalog()->getPages();
		$first_page = $pages->getPage(1);

		$page_width = $first_page->getWidth();
		$page_height = $first_page->getHeight();

		$preview_height = $this->extractor_pdf->document->get_preview()->height;
		$preview_width = $this->extractor_pdf->document->get_preview()->width;

		$width_factor = $page_width / $preview_width;
		$height_factor = $page_height / $preview_height;

		$x1 = $this->x * $width_factor;
		$x2 = ($this->x+$this->width) * $width_factor;
		$y1 = ($preview_height-$this->y) * $height_factor;
		$y2 = ($preview_height - ($this->y+$this->height)) * $height_factor;


		// the interresting part: initiate an extractor instance
		$extractor = new SetaPDF_Extractor($document);

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
		$this->value = $content;
		$this->save();
		return $content;
	}

	/**
	 * Get by Extractor_pdf
	 *
	 * @access public
	 * @param Extractor_Pdf $extractor_pdf
	 * @return array $extractor_fingerprints
	 */
	public static function get_by_extractor_pdf(Extractor_Pdf $extractor_pdf) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM extractor_pdf_fingerprint WHERE extractor_pdf_id=? ORDER BY sort', [ $extractor_pdf->id ]);
		$objects = [];
		foreach ($ids as $id) {
			$objects[] = self::get_by_id($id);
		}
		return $objects;
	}

}
