<?php
/**
 * Module Tag
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Web_Module_Tag extends \Skeleton\Package\Api\Web\Module\Call {

	/**
	 * Get by id
	 *
	 * Get a tag by his ID
	 *
	 * @access public
	 * @param int $id
	 * @return array $tag
	 */
	public function call_getById() {
		$tag = Tag::get_by_id($_REQUEST['id']);
		return $tag->get_info();
	}

	/**
	 * Get all
	 *
	 * Get the ids of all tags
	 *
	 * @access public
	 * @return array $tag_ids
	 */
	public function call_getAll() {
		$tags = Tag::get_all();
		$tag_ids = [];
		foreach ($tags as $tag) {
			$tag_ids[] = $tag->id;
		}
		return $tag_ids;
	}
}
