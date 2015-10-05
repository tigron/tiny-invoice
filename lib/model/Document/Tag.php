<?php
/**
 * Document_Tag class
 *
 * @author David Vandemaele <david@tigron.be>
 */

class Document_Tag {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;

	/**
	 * Get by document
	 *
	 * @access public
	 * @param Document $document
	 * @return array $items
	 */
	public static function get_by_document(Document $document) {
		$table = self::trait_get_database_table();
		$db = self::trait_get_database();
		$ids = $db->get_column('SELECT id FROM  ' . $table . ' WHERE document_id = ?', [ $document->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}
		return $items;
	}

	/**
	 * Get by document
	 *
	 * @access public
	 * @param Document $document
	 * @return array $items
	 */
	public static function get_by_tag(Tag $tag) {
		$table = self::trait_get_database_table();
		$db = self::trait_get_database();
		$ids = $db->get_column('SELECT id FROM ' . $table . ' WHERE tag_id = ?', [ $tag->id ]);

		$items = [];
		foreach ($ids as $id) {
			$items[] = self::get_by_id($id);
		}
		return $items;
	}
}
