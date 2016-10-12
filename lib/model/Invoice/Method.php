<?php
/**
 * Invoice_Method class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

abstract class Invoice_Method {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;

	abstract public function send(Invoice $invoice);

	/**
	 * Get by ID
	 *
	 * @access public
	 * @param int $id
	 */
	public static function get_by_id($id) {
		$db = Database::Get();
		$classname = $db->get_one('SELECT classname FROM invoice_method WHERE id=?', [ $id ]);
		$class = new $classname($id);

		return $class;
	}

	/**
	 * Get all
	 *
	 * @access public
	 */
	public static function get_all() {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM invoice_method', []);
		$result = [];

		try {
			$setting = Setting::get_by_name('enable_click_post');
			$click_post_enabled = $setting->value;
		} catch (Exception $e) {
			$click_post_enabled = false;
		}

		foreach ($ids as $id) {
			$invoice_method = self::get_by_id($id);

			if ($invoice_method->classname == 'Invoice_Method_Clickpost' and !$click_post_enabled) {
				continue;
			}
			$result[] = $invoice_method;
		}
		return $result;
	}

}
