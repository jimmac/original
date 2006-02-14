<?php
/* SQLite DB class for storing
   - image views,
	 - user comments
*/

$dbfile = "$root/$gallery_dir/photos.db";

//unfortunately in php4, the SQLiteDatabse class isn't created so we have to

class SQLiteDatabase {
	var $dbfile;

	function SQLiteDatabase ($dbfile) {
		
		$this->dbfile = $dbfile;
		//if db file doesn't exist, fill with skeleton
		if (file_exists($this->dbfile)) {
			$this->dbres = sqlite_open($this->dbfile, 0666, $sqliteerror);
		} else {
			//fill with skeleton
			$folder = dirname($this->dbfile);
			if (!is_writable($folder)) { //we need write permission to create database
				die("<p style=\"color:red;\">cannot create dabase. check permissions.</p>\n");
			} else {
				$this->dbres = sqlite_open($this->dbfile, 0666, $sqliteerror);
				//photo table
				$sql = "create table photo (id INTEGER PRIMARY KEY, caption TEXT, ";
				$sql .= "counter INTEGER, number INTEGER, album TEXT, name TEXT)";
				$this->query($sql);
				//comment table
				$sql = "create table comment (id INTEGER PRIMARY KEY, user TEXT, ";
				$sql .= "comment_body TEXT, photo_id INT, date DATETIME)";
				$this->query($sql);
			}
		}
	}

	function query($sql) {
		global $page;

		if (!$this->result = sqlite_query($this->dbres, $sql)) {
				print "Query failed, <span style=\"color: blue;\"><pre>$sql</pre></style>\n";
				print sqlite_error_string (sqlite_last_error($this->dbres));
				$page->footer();
				exit;
		}
	}

	function count() {
		return sqlite_num_rows($this->result);
	}

	function rewind() { //just to abstract from sqlite
		sqlite_rewind($this->result);
	}
	
}


$db = new SQLiteDatabase("$dbfile");

?>
