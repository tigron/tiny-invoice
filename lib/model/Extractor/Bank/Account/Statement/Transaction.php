<?php
/**
 * Extractor_Bank_Account_Statement_Transaction class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use \Skeleton\Database\Database;

class Extractor_Bank_Account_Statement_Transaction {
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Pager\Page;
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Delete;

	/**
	 * Validate
	 *
	 * @access public
	 * @param array $errors
	 * @return bool $validated
	 */
	public function validate(&$errors = []) {
		$required_fields = [ 'name' ];
		foreach ($required_fields as $required_field) {
			if (!isset($this->details[$required_field]) OR $this->details[$required_field] == '') {
				$errors[$required_field] = 'required';
			}
		}

		if (count($errors) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Parse content
	 *
	 * @access public
	 * @param Document $document (optional)
	 */
	public function extract_data(Bank_Account_Statement_Transaction $transaction = null) {
		if ($transaction === null) {
			$transaction = $this->bank_account_statement_transaction;
		}

		ob_start();
		set_error_handler(null);
		$return = eval($this->eval);
		$error = error_get_last();
		restore_error_handler();
		$output = ob_get_contents();
		ob_end_clean();

		if ( $return === false && $error ) {
			$exception = new Extractor_Eval_Exception();
			$exception->line = $error['line'];
			$exception->setMessage($error['message']);
			throw $exception;
		}

		$return = [
			'data' => $transaction->get_links(),
			'output' => $output
		];

		return $return;
	}

	/**
	 * Match
	 * Check if a given transaction matches the extractor fingerprints
	 *
	 * @access public
	 * @param Bank_Account_Statement_Transaction $transction
	 */
	public function match(Bank_Account_Statement_Transaction $transaction = null) {
		if ($transaction === null) {
			$transaction = $this->bank_account_statement_transaction;
		}

		if (trim($this->fingerprint_message) != '') {
			if (strpos(strtolower($transaction->get_message()), strtolower($this->fingerprint_message)) === false) {
				return false;
			}
		}

		if (trim($this->fingerprint_other_account_name) != '') {
			if (strpos(strtolower($transaction->other_account_name), strtolower($this->fingerprint_other_account_name)) === false) {
				return false;
			}
		}

		if (trim($this->fingerprint_other_account_number) != '') {
			if (strpos(strtolower($transaction->other_account_number), strtolower($this->fingerprint_other_account_number)) === false) {
				return false;
			}
		}
		$this->last_used = date('Y-m-d H:i:s');
		$this->save();
		return true;
	}
}
