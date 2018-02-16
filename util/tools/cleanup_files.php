<?php
include '../../config/global.php';

use \Skeleton\Database\Database;

$db = Database::get();

$ids = $db->get_column('
	SELECT id
	FROM file
	WHERE
	1
	AND id NOT IN (SELECT file_id FROM document WHERE preview_file_id IS NOT NULL)
	AND id NOT IN (SELECT preview_file_id FROM document WHERE preview_file_id IS NOT NULL)
	AND id NOT IN (SELECT file_id FROM creditnote)
	AND id NOT IN (SELECT file_id FROM export WHERE file_id IS NOT NULL)
	AND id NOT IN (SELECT file_id FROM incoming)
	AND id NOT IN (SELECT file_id FROM incoming_page)
	AND id NOT IN (SELECT preview_file_id FROM incoming_page)
	AND id NOT IN (SELECT file_id FROM invoice)
	AND id NOT IN (SELECT file_id FROM picture)
', []);

foreach ($ids as $id) {
	echo $id . "\n";
	$file = File::get_by_id($id);
	$file->delete();
}
