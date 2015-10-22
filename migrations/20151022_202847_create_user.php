<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Migration_20151022_202847_Create_user extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {

	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {
		$user = new User();
		$user->username = 'user';
		$user->set_password('user');
		$user->save();
	}
}
