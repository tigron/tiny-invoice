<?php
/**
 * Transaction Class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

abstract class Transaction {
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
	protected function __construct($id = null) {
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
		$details = $db->getRow('SELECT * FROM transaction WHERE id=?', array($this->id));
		if ($details === null) {
			throw new Controller_Exception('Transaction not found');
		}
		$details['data'] = unserialize($details['data']);
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
			$this->details['created'] = date('Y-m-d H:i:s');
			$where = false;
		} else {
			$mode = MDB2_AUTOQUERY_UPDATE;
			$where = 'id=' . $db->quote($this->id);
		}

		$this->details['data'] = serialize($this->details['data']);

		$db->autoExecute('transaction', $this->details, $mode, $where);

		if ($mode == MDB2_AUTOQUERY_INSERT) {
			$this->id = $db->getOne('SELECT LAST_INSERT_ID();');
		}
		$this->get_details();
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
		if ($key == 'completed' AND $value == true) {
			$this->executed = date('Y-m-d H:i:s');
			$this->details['completed'] = true;
		} else {
			$this->details[$key] = $value;
		}
	}

	/**
	 * Fail
	 *
	 * @access public
	 */
	public function fail() {
		$this->completed = true;
		$this->save();
	}

	/**
	 * Sleep
	 *
	 * @access public
	 * @param string $period (strtotime format)
	 */
	public function sleep($period) {
		$this->running_date = date('Y-m-d H:i:s', strtotime($this->running_date . ' + ' . $period));
		$this->save();
	}

	/**
	 * Get info
	 *
	 * @access public
	 * @return array $details
	 */
	public function get_info() {
		return $this->details;
	}

	/**
	 * Unfreeze
	 *
	 * @access public
	 */
	public function unfreeze() {
		Debug::send('unfreeze transaction ' . $this->id, $this->data);
		$this->frozen = false;
		$this->save();
	}

	/**
	 * Freeze
	 *
	 * @access public
	 */
	public function freeze() {
		$this->frozen = true;
		$this->save();
	}

	/**
	 * Abstract function run()
	 *
	 * @access private
	 */
	abstract protected function run();

	/**
	 * Get a Transaction by ID
	 *
	 * @access public
	 * @param $id
	 * @return Transaction
	 */
	public static function get_by_id($id) {
		$db = Database::Get();
		$type = $db->getOne('SELECT type FROM transaction WHERE id=?', array($id));
		if ($type === null) {
			throw new Controller_Exception('Transaction not found');
		}

		require_once LIB_PATH . '/model/Transaction/' . str_replace('_', '/', $type) . '.php';
		$classname = 'Transaction_' . $type;
		$class = new $classname($id);
		return $class;
	}

	/**
	 * Get runnable transactions
	 *
	 * @return array
	 * @access public
	 */
	public static function get_runnable() {
		$db = Database::Get();

		$transactions = array();
		$trans = $db->getCol('SELECT id FROM transaction WHERE running_date < NOW() AND completed=0 AND frozen=0 AND failed=0 AND locked=0');
		foreach ($trans as $id) {
			$transactions[] = Transaction::get_by_id($id);
		}
		return $transactions;
	}

	/**
	 * Run a transaction
	 *
	 * @access public
	 * @param Transaction $trans
	 */
	public static function run_transaction(Transaction $trans) {
		try {

			$trans->locked = true;
			$trans->save();

			echo '-------------------------------------------------------------------' . "\n";
			echo ' running transaction: ' . $trans->id . "\n";
			echo ' transaction type: ' . $trans->type . "\n";
			echo ' date: ' . date('d-m-Y H:i:s') . "\n";
			echo ' transaction data: ' . print_r($trans->data) . "\n";
			echo ' output: ' . "\n    ";
			ob_start();
			$trans->run();
			$output = ob_get_contents();
			ob_end_clean();
			echo str_replace("\n", "\n    ", $output);
			echo "\n";

			$trans->locked = false;
			$trans->save();

		} catch (Exception $e) {
			$trans->exception = print_r($e, true);
			Util_Debug::mail($trans->exception, 'Exception in execution of transaction ' . $trans->id);
			echo ' exception: ' . print_r($e) . "\n";
			$trans->failed = true;
			$trans->save();
		}
		echo '-------------------------------------------------------------------' . "\n\n\n";
	}
}
