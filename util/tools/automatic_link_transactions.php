<?php
include dirname(__FILE__) . '/../../config/global.php';

$transactions = Bank_Account_Statement_Transaction::get_unbalanced();
//$transactions = [ Bank_Account_Statement_Transaction::get_by_id(18799) ];

foreach ($transactions as $transaction) {
	if ($transaction->structured_message != '') {
		$message = $transaction->structured_message;
	} else {
		$message = $transaction->message;
	}
	echo $transaction->date . "\t" . $transaction->other_account_name . "\t" . $message;
	echo "\t";
	try {
		$transaction->automatic_link();
		echo 'success';
	} catch (Exception $e) {
		echo 'failed';
	}

	echo "\n";
}
