<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @author Lionel Laffineur <lionel@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20180511_152234_Tigron_codabox extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();

		$db->query("
			INSERT INTO `transaction` (`classname`, `created`, `data`, `retry_attempt`, `recurring`, `completed`, `failed`, `locked`, `frozen`, `parallel`) VALUES
			('Tigron_Coda',	'2016-04-25 13:48:06',	'\"\"',	0,	1,	1,	0,	0,	0,	0);",
		[]);

	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
