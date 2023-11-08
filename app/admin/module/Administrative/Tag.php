<?php
/**
 * Tag module
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Admin\Module\Administrative;

use \Skeleton\Application\Web\Template;
use \Skeleton\Application\Web\Module;
use \Skeleton\Core\Http\Session;
use \Skeleton\Pager\Web\Pager;

class Tag extends Module {

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

		if (isset($_POST) and count($_POST) > 0) {
			Session::redirect('/administrative/tag');
		}

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
		$tag = \Tag::get_by_id($_GET['id']);
		$tag->delete();

		Session::redirect('/administrative/tag');
	}

	/**
	 * Display Add
	 * Adds a new \user
	 *
	 * @access private
	 */
	protected function display_add() {
		$tag = new \Tag();
		$tag->load_array($_POST['tag']);
		$tag->save();

		Session::redirect('/administrative/tag');
	}

	/**
	 * Display edit
	 * Display the detailed information of a user
	 *
	 * @access private
	 */
	public function display_edit() {
		$template = Template::get();
		$tag = \Tag::get_by_id($_GET['id']);

		if (isset($_POST['tag'])) {
			$tag->load_array($_POST['tag']);
			$tag->save();

			Session::set_sticky('message', 'updated');
			Session::redirect('/administrative/tag?action=edit&id=' . $tag->id);
		}

		$template->assign('tag', $tag);
	}

	/**
	 * Search tag (ajax)
	 *
	 * @access public
	 */
	public function display_ajax_search() {
		$this->template = null;

		$pager = new Pager('tag');
		$pager->add_sort_permission('name');
		$pager->set_search($_GET['search']);
		$pager->page();

		$data = [];
		foreach ($pager->items as $tag) {
			$data[] = [
				'value' => $tag->id,
				'label' => $tag->name
			];
		}
		echo json_encode($data);
	}
	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		return 'admin.document';
	}
}
