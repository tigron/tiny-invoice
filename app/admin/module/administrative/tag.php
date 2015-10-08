<?php
/**
 * Tag management Actions module
 *
 * @package KNX-Web-Admin
 * @subpackage modules
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @version $Id: user.php 529 2010-03-16 17:09:26Z knx-onlineshop $
 */

use \Skeleton\Core\Web\Template;
use \Skeleton\Core\Web\Module;
use \Skeleton\Core\Web\Session;
use \Skeleton\Pager\Web\Pager;

class Web_Module_Administrative_Tag extends Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = 'administrative/tag.twig';

	/**
	 * Display
	 * This is the default method for a module.
	 *
	 * @access public
	 */
	public function display() {
		$pager = new Pager('Tag');

		if (isset($_POST['search'])) {
			$pager->set_search($_POST['search']);
		}

		$pager->add_sort_permission('name');

		$pager->page();

		$template = Template::Get();
		$template->assign('pager', $pager);
	}

	/**
	 * Display delete
	 * Deletes a user
	 *
	 * @access private
	 */
	protected function display_delete() {
		$tag = Tag::get_by_id($_GET['id']);
		$tag->delete();

		Session::Redirect('/administrative/tag');
	}

	/**
	 * Display Add
	 * Adds a new user
	 *
	 * @access private
	 */
	protected function display_add() {
		$tag = new Tag();
		$tag->load_array($_POST['tag']);
		$tag->save();

		Session::Redirect('/administrative/tag');
	}

	/**
	 * Display edit
	 * Display the detailed information of a user
	 *
	 * @access private
	 */
	public function display_edit() {
		$template = Template::Get();
		$tag = Tag::get_by_id($_GET['id']);

		if (isset($_POST['tag'])) {
			$tag->load_array($_POST['tag']);
			$tag->save();

			Session::set_sticky('message', 'tag_updated');
			Session::Redirect('/administrative/tag?action=edit&id=' . $tag->id);
		}
		$template->assign('tag', $tag);
	}

	/**
	 * Get all
	 *
	 * AJAX call
	 * @access public
	 */
	public function display_ajax_search_tag() {
		$pager = new Pager('tag');
		$pager->set_search($_GET['query']);
		$pager->add_sort_permission('tag.name');
		$pager->set_sort('tag.name');
		$pager->page(true);
		$tags = $pager->items;

		$data = array();
		foreach ($tags as $tag) {
			$data[] = array(
				'id' => $tag->id,
				'name' => $tag->name,
			);
		}
		echo json_encode($data);
		exit;
	}


}
