<?php
include '../../config/global.php';

use \Skeleton\Database\Database;

$db = Database::get();
$ids = $db->get_column('SELECT id FROM incoming_page WHERE incoming_id NOT IN (SELECT id FROM incoming)', []);

foreach ($ids as $id) {
	echo $id . "\n";
	$incoming_page = Incoming_Page::get_by_id($id);
	$incoming_page->delete();
}
