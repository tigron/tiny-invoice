<?php
/**
 * Module Tag
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

require_once LIB_PATH . '/model/Tag.php';
require_once LIB_PATH . '/base/Web/Pager.php';

class Module_Administrative_Tag extends Web_Module {
	/**
	 * Login required ?
	 * Default = yes
	 *
	 * @access public
	 * @var bool $login_required
	 */
	public $login_required = true;

	/**
	 * Template to use
	 *
	 * @access public
	 * @var string $template
	 */
	public $template = 'administrative/tag.twig';

	/**
	 * Display method
	 *
	 * @access public
	 */
	public function display() {
		$template = Web_Template::Get();

		$extra_conditions = array();
		if (isset($_POST['search'])) {
			$extra_conditions['%search%'] = $_POST['search'];
			$pager = new Web_Pager('tag', 'id', 'ASC', 1, $extra_conditions);
		} else {
			$pager = new Web_Pager('tag');
		}

		$template->assign('pager', $pager);
	}

	/**
	 * Add
	 *
	 * @access public
	 */
	public function display_add() {
		$template = Web_Template::Get();
		$this->template = 'snippet/form/tag.twig';

		if (isset($_POST['tag'])) {
			$tag = new Tag();
			$tag->load_array($_POST['tag']);
			if (!$tag->validate($errors)) {
				$template->assign('errors', $errors);
				$template->assign('tag', $tag);

			} else {
				$tag->save();
				Web_Session::Redirect('/administrative/tag');
			}
		}
	}

	/**
	 * Edit
	 *
	 * @access public
	 */
	public function display_edit() {
		$template = Web_Template::Get();
		$tag = Tag::get_by_id($_GET['id']);

		if (isset($_POST['tag'])) {
			$tag->load_array($_POST['tag']);
			$tag->save();
			$template->assign('saved', true);
		}

		$template->assign('tag', $tag);
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function display_delete() {
		$tag = Tag::get_by_id($_GET['id']);
		$tag->delete();
		Web_Session::Redirect('/administrative/tag');
	}
}
