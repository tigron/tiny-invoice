<?php
/**
 * Database Proxy Compatibility DB Class
 *
 * A compatibility layer to be complatible with the PEAR (M)DB(2) interfaces.
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

define('MDB2_AUTOQUERY_INSERT', 1);
define('MDB2_AUTOQUERY_UPDATE', 2);

require_once LIB_PATH . '/base/Database/Statement.php';

class Database_Proxy_Compatibility_DB extends Database_Proxy {
	/**
	 * Magic __call method to catch all calls to this object. Translates
	 * (M)DB(2) calls to something we support.
	 *
	 * @access public
	 * @param string $method Method to call
	 * @param array $arguments Arguments to pass to the method
	 * @return mixed $result Whatever we get back from the called method
	 */
	public function __call($method, $arguments) {
		if (!isset($arguments[1])) {
			$arguments[1] = array();
		}

		switch (strtolower($method)) {
			case 'getrow':              return $this->get_row($arguments[0], $arguments[1]); break;
			case 'getall':              return $this->get_all($arguments[0], $arguments[1]); break;
			case 'getcol':              return $this->get_column($arguments[0], $arguments[1]); break;
			case 'query':               return $this->query($arguments[0], $arguments[1]); break;
			case 'getone':              return $this->get_one($arguments[0], $arguments[1]); break;
			case 'autoexecute':         return $this->autoexecute($arguments); break;
			case 'quote':               return $this->quote($arguments[0]); break;
			case 'quoteidentifier':     return $this->quote_identifier($arguments[0]); break;
			case 'listtablefields':     return $this->get_columns($arguments[0], $arguments[1]); break;
			case 'escape':              return $this->escape($arguments[0]);
			case 'getdebugoutput':      return ''; break;
			default:                    return call_user_func_array(array($this->database, $method), $arguments);
		}
	}

	/**
	 * Helper method to emulate the (M)DB(2) autoexecute functionality.
	 * Basically just decides whether we need an INSERT or an UPDATE statement.
	 *
	 * @access private
	 * @param array $arguments Arguments to pass to INSERT or UPDATE queries
	 */
	private function autoexecute($arguments) {
		if ($arguments[2] == MDB2_AUTOQUERY_INSERT) {
			$this->insert($arguments[0], $arguments[1]);
		} else {
			$this->update($arguments[0], $arguments[1], $arguments[3]);
		}
	}
}
