<?php
/**
 * Database migration class
 *
 */


use \Skeleton\Database\Database;

class Migration_20231110_165029_Foreign_keys extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up(): void {
		$db = Database::get();

		$database = 'bookkeeping';

		$problems = 0;
		printf("Analyzing database structure and data:\n");
		$columns = $this->get_all_columns();
		foreach ($columns as $column) {
			$source_table = $column['Table'];
			$source_column = $column['Field'];
			if (substr($source_column, -3) !== '_id') {
				continue;
			}
			$remote_table = substr($source_column, 0, strlen($source_column) - 3);
			$remote_column = 'id';
			if ($this->table_exists($remote_table) === false) {
				continue;
			}
			$query = "SELECT " . $source_column . " FROM " . $source_table . " WHERE " . $source_column . " NOT IN ( SELECT " . $remote_column . " FROM " . $remote_table . " )";
			$ids = $db->get_column($query);
			if (count($ids) > 0) {
				printf("  - %s.%s -> %s.%s (%d orphan data)\n", $source_table, $source_column, $remote_table, $remote_column, count($ids));
				printf("    ids - (%s)\n", implode(',', $ids));
				$problems++;
			}
		}

		if ($problems > 0) {
			printf("\n%d problems were found preventing to create the foreign keys.  After they are fixed, run the migration again to proceed with the actual foreign keys creation.\n\n", $problems);
			throw new Exception("Foreign keys cannot be created");
		}

		printf("Removing existing foreign keys\n");
		$rows = $this->get_all_foreign_keys();
		foreach ($rows as $row) {
			$table_name = $row['table_name'];
			$constraint_name = $row['constraint_name'];
			printf("  - %s %s\n", $table_name, $constraint_name);
			$db->query("ALTER TABLE `" . $table_name . "` DROP FOREIGN KEY `" . $constraint_name . "`;");
		}

		printf("Converting id columns to unsigned int\n");
		$rows = $this->get_all_columns();
		foreach ($rows as $row) {
			$table_name = $row['Table'];
			$column_name = $row['Field'];
			$column_type = $row['Type'];
			$column_key = $row['Key'];
			$nullable = $row['Null'];
			if ($column_type === 'int(11)') {
				$query = "ALTER TABLE `" . $table_name . "` CHANGE `" . $column_name . "` `" . $column_name ."` int(11) unsigned ";
				if ($nullable === 'YES') {
					$query .= "NULL";
				} else {
					$query .= "NOT NULL";
				}
				if ($column_key === "PRI") {
					$query .= " auto_increment";
				}
				printf("  - %s.%s\n", $table_name, $column_name);
				//printf("[%s]\n", $query);
				$db->query($query);
			}
		}

		printf("Creating foreign keys:\n");
		foreach ($columns as $column) {
			$source_table = $column['Table'];
			$source_column = $column['Field'];
			if (substr($source_column, -3) !== '_id') {
				continue;
			}
			$remote_table = substr($source_column, 0, strlen($source_column) - 3);
			$remote_column = 'id';
			if ($this->table_exists($remote_table) === false) {
				continue;
			}

			$query = "ALTER TABLE `" . $source_table . "` ADD FOREIGN KEY (`" . $source_column . "`) REFERENCES `" . $remote_table . "` (`" . $remote_column . "`)";
			//printf("%s\n", $query);
			try {
				printf("Adding foreign key: %s.%s -> %s.%s\n", $source_table, $source_column, $remote_table, $remote_column);
				$db->query($query);
			} catch (Exception $e) {
				printf("\033[91m%s\n\n\033[39m", $e->getMessage());
			}
		}
		
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down(): void {

	}

	/**
	 * table_exists
	 *
	 * @access private
	 * @param string $table_name
	 * @return bool
	 */
	private function table_exists(string $table_name): bool {
		$db = Database::get();
		$tables = $db->get_column("SHOW TABLES");
		foreach ($tables as $table) {
			if ($table === $table_name) {
				return true;
			}
		}
		return false;
	}

	/**
	 * get_all_columns
	 *
	 * @access private
	 * @return array
	 */
	private function get_all_columns(): array {
		$db = Database::get();
		$results = [];
		$tables = $db->get_column("SHOW TABLES");
		foreach ($tables as $table) {
			$columns = $db->get_all("SHOW COLUMNS FROM " . $table);
			foreach ($columns as $column) {
				$column['Table'] = $table;
				$results[] = $column;
			}
		}
		return $results;
	}

	/**
	 * get all foreign keys
	 *
	 * @access private
	 * @return array
	 */
	private function get_all_foreign_keys(): array {
		$db = Database::get();
		$results = [];
		$tables = $db->get_column("SHOW TABLES");
		foreach ($tables as $table) {
			$sql = $db->get_all("SHOW CREATE TABLE " . $table);
			$sql = array_shift($sql);
			$sql = $sql['Create Table'];
			$lines = explode("\n", $sql);
			foreach ($lines as $line) {
				if (strpos($line, 'CONSTRAINT') !== false) {
					$items = explode('`', $line);
					$results[] = [
						"table_name" => $table,
						"column_name" => $items[3],
						"remote_table" => $items[5],
						"remote_column" => $items[7],
						"constraint_name" => $items[1],
					];
				}
			}
		}
		return $results;
	}
}
