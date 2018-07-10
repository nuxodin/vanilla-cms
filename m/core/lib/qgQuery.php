<?php
namespace qg;

//require_once 'core/lib/qgQueryFrom.php';
//require_once 'core/lib/qgQuerySelect.php';
//require_once 'core/lib/qgQueryEntry.php';

class qgQuery {
	public $wheres    = [];
	public $orders    = [];
	public $limit_num = null;

	function __construct($t) {
		$this->Db = D();
		$this->Table = $this->Db->$t;
		$this->From = $this->addFrom($t);
		foreach ($this->Table->getPrimaries() as $Pri) {
			$this->From->select($Pri);
		}
	}
	function select($select, $alias = null, $From = null) {
		$Select = new qgQuerySelect($this, $select, $alias, $From);
		return $this->Selects[(string)$Select] = $Select;
	}
	function nextSelectAlias() { static $i = 1; return 's_'.$i++; }
	function leftJoin ($table, $where = null, $alias = null) { return $this->addFrom($table, $where, 'LEFT',  $alias); }
	function innerJoin($table, $where = null, $alias = null) { return $this->addFrom($table, $where, 'INNER', $alias); }
	function addFrom($table, $where = null, $join = 'left', $alias = null) {
		$From = new qgQueryFrom($this, $table, $where, $join, $alias);
		return $this->Froms[(string)$From] = $From;
	}
	function getSqlFrom() {
		foreach ($this->Froms AS $alias => $From)
			$froms[] = $From === $this->From ? $From." ".$From->alias : $From->join.' JOIN '.$From->Table." ".$From." ON ".$From->getOn();
		return implode("\n\t", $froms);
	}
	function nextFromAlias() { static $i = 1; return 'f_'.$i++; }
	function addWhere($s) {
		$this->wheres[] = " (".$s.") ";
	}
	function orderBy($f, $direction = null) {
		$f=(string)$f;
		unset($this->orders[$f]);
		$this->orders[$f] = $direction !== 'DESC'?'ASC':'DESC';
	}
	function limit($num = null, $offset = 0) {
		$this->limit_num 	= $num;
		$this->limit_offset = $offset > -1 ? $offset : 0;
	}
	function sql($addWhere = 1) {
		$wheres = $this->wheres;
		$wheres[] = $addWhere; // zzz ?
		foreach ($this->Selects AS $alias => $Select)  { $selects[] = $Select->formel()." AS ".$alias; }
		foreach ($this->orders AS $field => $d)        { $orders[] = $field." ".$d; }
		foreach ($this->Table->getPrimaries() AS $Pri) { $groupby[] 	= $this->From.".".$Pri; }
		$sql =
			"SELECT SQL_CALC_FOUND_ROWS \n\t".implode(",\n\t ", $selects)." 	\n".
			"FROM  	\n\t".$this->getSqlFrom()." 								\n".
			(count($wheres)?
			"WHERE 	\n\t".implode("\n\t  AND ", $wheres)." 						\n":"").
			//"GROUP BY \n\t".implode(",\n\t", $groupby)." 	\n".
			//"GROUP BY 'a'														\n".
			(isset($orders)?
			"ORDER BY \n\t".implode(",\n\t", $orders)."							\n":"").
			($this->limit_num!==null?
			"LIMIT ".(int)$this->limit_offset.', '.(int)$this->limit_num."		\n":"");
		return $sql;
	}
	function update($eid,$values) {
		$wheres   = $this->wheres;
		$wheres[] = $this->Table->entryId2where($eid, $this->From->alias);
		$sets = [];
		foreach ($values as $alias => $value) {
			if (!isset($this->Selects[$alias])) continue;
			$sets[] = $this->Selects[$alias]->formel().' = '.$this->Selects[$alias]->Field->valueToSql($value);;
		}
		$sql =
			"UPDATE  	\n\t".$this->getSqlFrom()." 							\n".
			"SET 	\n\t".implode(",", $sets)." 								\n".
			(count($wheres)?
			"WHERE 	\n\t".implode("\n\t  AND ", $wheres)." 						\n":"").
			"";
		$this->Db->query($sql);
		return 1;
	}
	function numRows($addWhere = 1) {
		$this->Db->query($this->sql($addWhere));
		return $this->Db->one("SELECT FOUND_ROWS()");
	}
	function get($addWhere = 1) {
		$Res = $this->Db->query($this->sql($addWhere));
		if (!$Res) return false;
		$ret = [];
		while ($vs = $Res->fetch(\PDO::FETCH_ASSOC)) {
			$id = $this->From->Table->entryId($vs); // todo: should work with aliases
			$ret[$id] = new qgQueryEntry($this, $id, $vs);
		}
		return $ret;
	}
	function getById($id) {
		$vs = $this->get($this->Table->entryId2where($id, $this->From->alias));
		return array_shift($vs);
	}
	function delete($entryId) {
		$values = $this->Table->delete($entryId);
	}
	function insert($vs) {
		foreach ($this->Selects AS $alias => $Select) {
			if (!isset($vs[$alias]) && $Select->getInsertValue())
			$vs[$alias] = $Select->getInsertValue();
		}
		return $this->From->insert($vs);
	}
}
