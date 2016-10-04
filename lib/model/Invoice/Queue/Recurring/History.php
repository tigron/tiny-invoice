<?php
/**
 * Invoice_Queue_Recurring_History Class
 *
 * @package KNX-lib
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 * @version $Id$
 */

class Invoice_Queue_Recurring_History {

	/**
	 * @var int $id
	 * @access public
	 */
	public $id;

	/**
	 * Details
	 *
	 * @var array @details
	 * @access private
	 */
	private $details;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param int $id
	 */
	public function __construct($id = null) {
		if ($id !== null) {
			$this->id = $id;
			$this->get_details();
		}
	}

	/**
	 * Get details
	 *
	 * @access private
	 */
	private function get_details() {
		$db = Database::Get();
		$details = $db->getRow('SELECT * FROM invoice_queue_recurring_history WHERE id=?', array($this->id));
		if ($details === null) {
			throw new Controller_Exception('Invoice_Queue_Recurring_History not found');
		}
		$this->details = $details;
	}

	/**
	 * Save
	 *
	 * @access public
	 */
	public function save() {
		$db = Database::Get();
		if (!isset($this->id)) {
			$mode = MDB2_AUTOQUERY_INSERT;
			$where = false;
			$this->details['created'] = date('Y-m-d H:i:s');
		} else {
			$mode = MDB2_AUTOQUERY_UPDATE;
			$where = 'id=' . $db->quote($this->id);
		}

		$db->autoExecute('invoice_queue_recurring_history', $this->details, $mode, $where);

		if ($mode == MDB2_AUTOQUERY_INSERT) {
			$this->id = $db->getOne('SELECT LAST_INSERT_ID();');
		}
		$this->get_details();
	}

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		$db = Database::Get();
		$db->query('DELETE FROM invoice_queue_recurring_history WHERE id=?', array($this->id));
	}

	/**
	 * Get
	 *
	 * @access public
	 * @param string $key
	 */
	public function __get($key) {
		if (!isset($this->details[$key])) {
			throw new Controller_Exception('Unknown key requested: '. $key);
		} else {
			return $this->details[$key];
		}
	}

	/**
	 * Set
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value) {
		$this->details[$key] = $value;
	}

	/**
	 * Get info
	 *
	 * @access public
	 * @return array $details
	 */
	public function get_info() {
		$details = $this->details;
		return $details;
	}

	/**
	 * Get a Invoice_Queue_Recurring_History by ID
	 *
	 * @access public
	 * @param $id
	 * @return Invoice_Queue_Recurring_History
	 */
	public static function get_by_id($id) {
		return new Invoice_Queue_Recurring_History($id);
	}
}
?>
