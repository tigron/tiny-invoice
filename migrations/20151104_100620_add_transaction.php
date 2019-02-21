<?php
/**
 * Database migration class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Migration_20151104_100620_Add_transaction extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("INSERT INTO `transaction` (`classname`, `created`, `scheduled_at`, `executed_at`, `data`, `completed`, `failed`, `locked`, `frozen`) VALUES ('Mailscanner', now(), now(), now(), '', '0', '0', '0', '0');");
		$db->query("INSERT INTO `transaction` (`classname`, `created`, `scheduled_at`, `executed_at`, `data`, `completed`, `failed`, `locked`, `frozen`) VALUES ('Reminder_Invoice', now(), now(), now(), '', '0', '0', '0', '0');");
		$db->query("INSERT INTO `transaction` (`classname`, `created`, `scheduled_at`, `executed_at`, `data`, `completed`, `failed`, `locked`, `frozen`) VALUES ('Reminder_Purchase', now(), now(), now(), '', '0', '0', '0', '0');");
		$db->query("INSERT INTO `transaction` (`classname`, `created`, `scheduled_at`, `executed_at`, `data`, `completed`, `failed`, `locked`, `frozen`) VALUES ('Cleanup_File', now(), now(), now(), '', '0', '0', '0', '0');");
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
