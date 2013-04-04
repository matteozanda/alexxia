<?php

abstract class ALEDatabase {

	protected static $h,$u,$p,$db,$pre;
	
	protected static $connection=null;
	
	public static function params($h,$u,$p,$db,$pre='') {
		self::$h = $h;
		self::$u = $u;
		self::$p = $p;
		self::$db = $db;
		self::$pre = $pre;
	}
	
	public static function create($name,$dim=5) {
		return new ALETable($name,$dim,true);
	}
	
	public static function q_rows($q) {
		return static::rows(static::query(self::create_query(func_get_args())));
	}
	
	public static function q_assoc($q) {
		return static::assoc(static::query(self::create_query(func_get_args())));
	}
	
	//Metodi astratti
	
	abstract static function connect();
	abstract static function read($name);
	abstract static function create_query($argv);
	abstract static function query();
	abstract static function error();
	abstract static function rows($r);
	abstract static function assoc($r);
	abstract static function select($cols,$from);
	
}

class DB extends ALEDatabase {
	
	public static function connect() {
		if (self::$connection==null) {
			self::$connection = mysql_connect(self::$h,self::$u,self::$p);
			mysql_select_db(self::$db,self::$connection);
		}
	}
	
	public function __destruct() {
		mysql_close(self::$connection);
	}
	
	public static function read($name) {
		//Lettura di una tabella
		$table_info = self::query('SHOW COLUMNS FROM `'.$name.'`');
		$table = new ALETable($name,0,$this,false);
		while ($r = self::assoc($table_info)) {
			$type_d = explode(' ',$r['Type']);
			$type_d2 = explode('(',$type_d[0]);
			$column = $table->property($r['Field'])->type($type_d2[0]);
			if ($type_d2[1]!='')
				$column->dimension(substr($type_d2[1],0,-1));
			$c = count($type_d);
			for ($i=1;$i<$c;$i++) 
				switch($type_d[$i]) {
					case 'unsigned' : $column->unsigned(); break;
					case 'zerofill' : $column->zerofill(); break;
				}
			if ($r['Null'] == 'NO')
				$column->not_null();
			$extra = explode(' ',$r['Extra']);
			foreach($extra as $e)
				switch($e) {
					case 'auto_increment' : $column->auto_increment(); break;
				}
		}
		$table_info2 = self::query('SHOW INDEX FROM `'.$name.'`');
		$uniques = array();
		while ($r = self::assoc($table_info2)) {
			if ($r['Key_name'] != 'PRIMARY')
				$uniques[$r['Key_name']][] = $r['Column_name'];
		}
		foreach($uniques as $v)
			call_user_func_array(array($table, "make_unique"), $v);
		return $table;
	}
	
	public static function create_query($argv) {
	    $argc = count($argv);
		$q = '';
		for ($i = 0; $i < $argc; $i++) { 
			if ($i&1) {
				if (is_array($argv[$i])) {
					$aux = "";
					foreach($argv[$i] as $x)
						$aux .= "'".mysql_real_escape_string($x)."',";
					$q .= trim($aux,",");
				} else {
					$q .= "'".mysql_real_escape_string($argv[$i])."' ";
				}
			} else
				$q .= $argv[$i];
		}
		return $q; 
	}
	
	public static function query() {
		self::connect();
		return mysql_query(self::create_query(func_get_args()),self::$connection);
	}
	
	public static function insert($t,$el) {
		$camps = '';
		$values = array();
		$i=0;
		foreach ($el as $k => $v) {
			$camps.='`'.$k.'`,';
			$j=0;
			if (is_array($v)) {
				$tot = count($v);
				for(;$j<$tot;$j++)
					$values[$j][$i] = '"'.mysql_real_escape_string($v[$j]).'"'; 
			} else
				$values[0][$i] = '"'.mysql_real_escape_string($v).'"';
			$i++;
		}
		$tot = count($values);
		for ($j=0;$j<$tot;$j++) {
			for ($x=0;$x<$i;$x++)
				if (!isset($values[$j][$x]))
					$values[$j][$x] = '""';
			$values[$j] = '('.implode(',',$values[$j]).')';
		}
		return self::query('INSERT INTO `'.$t.'` ('.substr($camps,0,-1).') VALUES '.implode(',',$values));
	}
	
	public static function update($t,$el) {
		$set = array();
		foreach ($el as $k=>$v)
			$set[] = '`'.$k.'` = "'.mysql_real_escape_string($v).'"';
		return self::query('UPDATE `'.(self::$pre).$t.'` SET '.implode(' AND ',$set).' '.(self::create_query(array_slice(func_get_args(),2))));
	}
	
	public static function select($cols,$from) {
		$set = array();
		return print('SELECT '.$cols.' FROM `'.(self::$pre).$from.'` '.(self::create_query(array_slice(func_get_args(),2))));
	}
	
	public static function error() {
		return mysql_error(self::$connection);
	}
	
	public static function rows($r) {
		return mysql_num_rows($r);
	}
	
	public static function assoc($r) {
		return mysql_fetch_assoc($r);
	}

}
include(__base_path.'config/dbconfig.php');
?>