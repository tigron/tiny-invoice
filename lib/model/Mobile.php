<?php
/**
 * Mobile class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Mobile {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete;
	use \Skeleton\Pager\Page;

	/**
	 * Create token
	 *
	 * @access public
	 */
	public function create_token() {
		do {
			$token = Util::create_random_code(64);

			try {
				self::get_by_token($token);
				$exists = true;
			} catch (Exception $e) {
				$exists = false;
			}
		} while ($exists == true);
		$this->token = $token;
		$this->save();
	}

	/**
	 * Get by token
	 *
	 * @access public
	 * @param string $token
	 * @return Mobile $mobile
	 */
	public static function get_by_token($token) {
		$db = Database::get();
		$id = $db->get_one('SELECT id FROM mobile WHERE token=?', [ $token ]);
		if ($id === null) {
			throw new Exception('Mobile not found');
		}
		return self::get_by_id($id);
	}


	/**
	 * Get by user
	 *
	 * @access public
	 * @param User $user
	 * @return array $mobiles
	 */
	public static function get_by_user(User $user) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM mobile WHERE user_id=?', [ $user->id ]);
		$mobiles = [];
		foreach ($ids as $id) {
			$mobiles[] = self::get_by_id($id);
		}
		return $mobiles;
	}

	/**
	 * Get by user
	 *
	 * @access public
	 * @param User $user
	 * @return array $mobiles
	 */
	public static function get_registered_by_user(User $user) {
		$db = Database::get();
		$ids = $db->get_column('SELECT id FROM mobile WHERE user_id=? AND registered is not null', [ $user->id ]);
		$mobiles = [];
		foreach ($ids as $id) {
			$mobiles[] = self::get_by_id($id);
		}
		return $mobiles;
	}

}
