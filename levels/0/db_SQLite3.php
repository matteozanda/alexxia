<?php
include_once('db_SQL.php');

class ALESQLite3 extends ALESQLDatabase {

	public function SQLEscape($q) {
		return SQLite3::escapeString($q);
	}
	
	public function connect() {
		if ($this->connection==null)
			$this->connection = new SQLite3(__base_path.'config/'.($this->db).'.db3');
	}
	
	public function query() {
		$this->connect();
		return $this->connection->query($this->create_query(func_get_args()));
	}	
	
	public function error() {
		return $this->connection->lastErrorMsg();
	}
	
	public function rows($r) {
		return 0; //non facilmente implementabile
	}
	
	public function assoc($r) {
		return $r->fetchArray(SQLITE3_ASSOC)
	}

}
?>