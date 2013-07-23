<?php
/**
 * trait: Save
 *
 * @author Christophe Gosiau <christophe.gosiau@tigron.be>
 * @author Gerry Demaret <gerry.demaret@tigron.be>
 */

class Exception_Validation extends Exception {}

trait Save {
	/**
	 * Save the object
	 *
	 * @access public
	 */
	public function save() {
		// If we have a validate() method, execute it
		if (is_callable(array($this, 'validate'))) {
			if ($this->validate() === false) {
				throw new Exception_Validation();
			}
		}

		$table = self::trait_get_database_table();

		$db = self::trait_get_database();

		if (!isset($this->id) OR $this->id === null) {
			$mode = MDB2_AUTOQUERY_INSERT;
			$this->details['created'] = date('Y-m-d H:i:s');
			$where = false;

			if (is_callable(array($this, 'pre_insert'))) {
				$this->pre_insert();
			}
		} else {
			$mode = MDB2_AUTOQUERY_UPDATE;
			$this->details['updated'] = date('Y-m-d H:i:s');
			$where = 'id=' . $db->quote($this->id);
		}

		$db->autoExecute($table, $this->details, $mode, $where);

		if ($mode === MDB2_AUTOQUERY_INSERT) {
			$this->id = $db->getOne('SELECT LAST_INSERT_ID();');
		}

		$this->get_details();
	}
}
