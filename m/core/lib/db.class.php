<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

namespace qg;

class db {

	public $_Tables = [];

	function __construct($conn, $user, $pass) {
		$this->PDO = new \PDO($conn, $user, $pass);
		$this->PDO->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$this->PDO->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		$this->PDO->exec('SET NAMES utf8');
		$this->PDO->exec('SET time_zone = "'.date('P').'"');
		//$this->PDO->exec('SET SESSION sql_mode = \'TRADITIONAL\''); // todo
		$this->PDO->exec('SET SESSION sql_mode = \'NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\'');

		$this->Cache = cache($conn.'_dbTables');
	}
	function __get($n) {
		!$this->_Tables && $this->Tables();
		if (isset($this->_Tables[$n])) return $this->_Tables[$n];
		return false;
	}
	function Tables() {
		if (!$this->_Tables) {
			if (!$this->Cache->get($tables)) {
				$tables = [];
				foreach ($this->col("SHOW TABLES") AS $n) {
					$tables[$n] = $n;
					/* at the moment qg_db_table is not used!!!!
					$qgData = $this->row("SELECT * FROM qg_db_table WHERE name = ".$this->quote($n));
					if (!$qgData) {
						$this->query("INSERT INTO qg_db_table SET name = ".$this->quote($n));
						$qgData = $this->row("SELECT * FROM qg_db_table WHERE name = ".$this->quote($n));
					}
					*/
				}
				!isset($tables['qg_db_table']) && $this->_setup();
			}
			foreach ($tables AS $table) {
				$this->_Tables[$table] = new dbTable($this, $table);
			}
		}
		return $this->_Tables;
	}
	private function _setup() {
		$this->query("
		CREATE TABLE IF NOT EXISTS qg_db_table (
		  name varchar(255) NOT NULL,
		  title int(10) NOT NULL,
		  description int(10) NOT NULL,
		  PRIMARY KEY (name)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8; ");
		$this->query("
		CREATE TABLE IF NOT EXISTS qg_db_field (
		  id int(10) NOT NULL auto_increment,
		  name varchar(160) NOT NULL,
		  tab varchar(160) NOT NULL,
		  title int(10) NOT NULL,
		  description int(10) NOT NULL,
		  handler varchar(32) NOT NULL,
		  parent varchar(255) NOT NULL,
		  on_parent_delete varchar(128) NOT NULL,
		  on_parent_copy varchar(128) NOT NULL,
		  PRIMARY KEY  (name,tab),
		  UNIQUE KEY id (id),
		  KEY tab (tab),
		  KEY name (name),
		  KEY on_parent_delete (on_parent_delete),
		  KEY on_parent_copy (on_parent_copy)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8; ");
		$this->Cache->remove();
		$this->_Tables = null;
		$this->Tables();
	}
	/*
	function _sync() {
		$qg = [];
		foreach ($this->all("SELECT * FROM qg_db_table") as $vs)
			$qg[$vs['name']] = 1;
		$real = [];
		foreach ($this->col('SHOW TABLES') AS $n) {
			!isset($qg[$n]) && $this->query("INSERT INTO qg_db_table SET name = ".$this->quote($n));
			$real[$n] = 1;
		}
		foreach ($qg as $n => $egal)
			!isset($real[$n]) && $this->removeTable($n);
			//!isset($real[$n]) && $this->query("DELETE FROM qg_db_table WHERE name = ".$this->quote($n));
	}
	*/
	function addTable($name) {
		$this->query("CREATE TABLE IF NOT EXISTS ".$name." ( _qgtmp varchar(0) NOT NULL ) ENGINE = MYISAM"); // mysam is faster 10.5.13
		$this->query("INSERT IGNORE INTO qg_db_table SET name = ".$this->quote($name));
		$this->_Tables = null;
		$this->Cache->remove();
		return $this->$name;
	}
	function removeTable($name) { // todo test
		$this->query("DROP TABLE IF EXISTS ".$name);
		$this->query("DELETE FROM qg_db_table WHERE name = ".$this->quote($name));
		$this->query("DELETE FROM qg_db_field WHERE tab = ".$this->quote($name));
		$this->_Tables = null;
		$this->Cache->remove();
	}
	function query($sql) {

// $start = microtime(1);

		$r = $this->PDO->query($sql);

// echo "\n".trim(preg_replace('/\s+/',' ',$sql))."\n";
// $diff = (microtime(1) - $start)*1000-0.0015;
// echo number_format($diff, 2)."\n\n";
// static $sum = 0;
// // $sum += $diff;
// // echo number_format($sum, 2)."\n";

		if ($this->PDO->errorCode() !== "00000") {
			$x = $this->PDO->errorInfo();
			$GLOBALS['skip_stacks'] += 1;
			trigger_error('mysql: '.$x[2]." <br>\n".preg_replace('/\s+/', ' ', $sql));
			$GLOBALS['skip_stacks'] -= 1;
			//exit();
		}
		return $r;
	}
	function all($sql) {
		$GLOBALS['skip_stacks'] += 1;
		$st = $this->query($sql);
		$GLOBALS['skip_stacks'] -= 1;
		$ret = [];
		if ($st) // todo: test if $st needed?
			foreach ($st as $vs)
				$ret[] = $vs;
		return $ret;
	}
	function row($sql) {
		$GLOBALS['skip_stacks'] += 1;
		$st = $this->query($sql);
		$GLOBALS['skip_stacks'] -= 1;
		if ($st) // todo: test if $st needed?
			return $st->fetch();
	}
	function col($sql) {
		$ret = [];
		$GLOBALS['skip_stacks'] += 1;
		foreach ($this->query($sql) as $vs) $ret[] = array_shift($vs);
		$GLOBALS['skip_stacks'] -= 1;
		return $ret;
	}
	function indexCol($sql) {
		$ret = [];
		$GLOBALS['skip_stacks'] += 1;
		$res = $this->query($sql);
		$GLOBALS['skip_stacks'] -= 1;
		foreach ($res as $vs) {
			$i = array_shift($vs);
			$v = array_shift($vs);
			$ret[$i] = $v;
		}
		return $ret;
	}
	function one($sql) {
		$GLOBALS['skip_stacks'] += 1;
		$row = (array)$this->row($sql);
		$GLOBALS['skip_stacks'] -= 1;
		foreach ($row as $v) return $v;
	}
	function lastInsertId() {
		return $this->PDO->lastInsertId();
	}
	function quote($v) {
		return $this->PDO->quote($v);
	}
	function prepare($sql) {
		return $this->PDO->prepare($sql);
	}

	function importXml($xml) {
		$dom = new \DomDocument();
		$dom->loadXML($xml);
		$node = $dom->firstChild;
		return $this->_importXmlEntryNode($node);
	}
	function _importXmlEntryNode($node) {
		$table = $node->getAttributeNode('table')->nodeValue;
		return $this->$table->_importXmlNode($node->childNodes);
	}

	static function _array_to_column_definition($data) {
		$data['type']          = $data['type']          ?? 'varchar';
		$data['length']        = $data['length']        ?? false;
		$data['special']       = $data['special']       ?? '';
		$data['null']          = $data['null']          ?? false;
		$data['autoincrement'] = $data['autoincrement'] ?? false;
		$data['default']       = $data['default']       ?? false;

		$data['type']    = trim(strtoupper($data['type']));
		$data['special'] = trim(strtoupper($data['special']));

		$vs = ['VARCHAR','TINYINT','TEXT','DATE','SMALLINT','MEDIUMINT','INT','BIGINT','FLOAT','DOUBLE','DECIMAL','DATETIME','TIMESTAMP','TIME','YEAR','CHAR','TINYBLOB','TINYTEXT','BLOB','MEDIUMBLOB','MEDIUMTEXT','LONGBLOB','LONGTEXT','BOOL','BINARY'];
		if (array_search($data['type'], $vs) === false) throw new \Exception('field type not "'.$data['type'].'" allowed');

		$vs = ['','UNSIGNED','UNSIGNED ZEROFILL','ON UPDATE CURRENT_TIMESTAMP'];
		if (array_search($data['special'], $vs) === false) throw new \Exception('field special not "'.$data['special'].'" allowed');

		$length = $data['length'] ? '('.$data['length'].')' : ''; // sql injection?

		if (in_array($data['type'], ['DATE','DATETIME','FLOAT','TEXT','TINYTEXT','MEDIUMTEXT','LONGTEXT'])) $length = '';
		if ($data['type'] === 'VARCHAR' && !$length)             $length = '(255)';
		if ($data['type'] === 'DECIMAL' && $data['length'] > 65) $length = '(12,8)';

		$default = $data['autoincrement'] ? 'AUTO_INCREMENT' : ($data['default'] ? "DEFAULT ".D()->quote($data['default']) : ""); // todo: very bad: D() is used
		$str = " ".$data['type'].$length." ".$data['special']." ".($data['null']?'NULL':'NOT NULL')." ".$default;
		return $str;
	}
}
