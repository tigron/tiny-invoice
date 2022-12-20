<?php
/**
 * Module File
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Api\Module\Document\Incoming;

use \Skeleton\Pager\Web\Pager;

class Invoice extends \Skeleton\Package\Api\Web\Module\Call {

	/**
	 * Get by id
	 *
	 * Get an incoming invoice by his ID
	 *
	 * @access public
	 * @param int $id
	 * @return array $document
	 */
	public function call_getById() {
		$document = \Document::get_by_uuid($_REQUEST['id']);
		if ($document->classname != 'Document_Incoming_Invoice') {
			throw new \Exception('incorrect document');
		}
		if (!$document->available_for_api()) {
			throw new \Exception('Not available');
		}
		return $document->get_info();
	}

	/**
	 * Get PDF
	 *
	 * Get the PDF document of an incoming invoice by his ID
	 *
	 * @access public
	 * @param int $id
	 * @return array $fileinfo
	 */
	public function call_getPdf() {
		$document = \Document::get_by_uuid($_REQUEST['id']);
		if ($document->classname != 'Document_Incoming_Invoice') {
			throw new \Exception('incorrect document');
		}
		if (!$document->available_for_api()) {
			throw new \Exception('Not available');
		}
		return $document->file->get_info();
	}

	/**
	 * Get by tag_ids
	 *
	 * Get incoming invoices by their tag ids
	 *
	 * @access public
	 * @param string $tag_ids Comma seperated ids
	 * @return array $ids ids of incoming_invoices
	 */
	public function call_getByTagIds() {
		$pager = new Pager('document');
		$pager->add_condition('classname', 'Document_Incoming_Invoice');
		$pager->add_join('document_tag', 'document_id', 'document.id');
		$pager->add_condition('document_tag.tag_id', 'IN', $_REQUEST['tag_ids']);
		$pager->page(true);
		$items = $pager->items;
		$ids = [];
		foreach ($items as $item) {
			if ($item->available_for_api()) {
				$ids[] = $item->id;
			}
		}
		return $ids;
	}

	/**
	 * Get all
	 *
	 * Get the ids of all the incoming invoices
	 *
	 * @access public
	 * @return array $ids ids of incoming_invoices
	 */
	public static function call_getAll() {
		return \Document_Incoming_Invoice::get_all_ids();
	}
}
