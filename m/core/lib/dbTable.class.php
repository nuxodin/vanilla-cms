<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class dbTable {

	public $Fs            = null;
	public $Primaries     = [];
	public $AutoIncrement = false;
	public $_name;

	function __construct($Db, $name) {
		$this->Db    = $Db;
		$this->_name = (string)$name;
		$this->fieldsCache = cache('dbFields', $name);
	}
	function __get($n) {
		$this->Fs === null && $this->Fields();
		return $this->Fs[$n] ?? false;
	}
	function Fields() {
		if ($this->Fs === null) {
			if (!$this->fieldsCache->get($fields)) {
				foreach ($this->Db->query("SHOW FIELDS FROM ".$this) as $values) {
					$name = $values['Field'];
					$fields[$name] = $values;
					$row = $this->Db->row("SELECT * FROM qg_db_field WHERE tab = ".$this->Db->quote($this)." AND name = ".$this->Db->quote($name));
					if (!$row) {
						$this->Db->query("INSERT INTO        qg_db_field SET   tab = ".$this->Db->quote($this).",    name = ".$this->Db->quote($name));
						$row = $this->Db->row("SELECT * FROM qg_db_field WHERE tab = ".$this->Db->quote($this)." AND name = ".$this->Db->quote($name));
					}
					$fields[$values['Field']] += $row;
				}
			}
			foreach ($fields as $name => $field) {
				$this->Fs[$name] = new dbField($this, $name, $field);
				if ($this->Fs[$name]->isPrimary())       { $this->Primaries[$name] = $this->Fs[$name]; }
				if ($this->Fs[$name]->isAutoIncrement()) { $this->AutoIncrement    = $this->Fs[$name]; }
			}
			//isset($this->Fs['_qgtmp']) && count($this->Fs) > 1 && $this->Db->query("ALTER TABLE ".$this." DROP _qgtmp"); // zzz
			isset($this->Fs['_qgtmp']) && count($this->Fs) > 1 && $this->remField('_qgtmp');
		}
		return $this->Fs;
	}
	function getPrimaries() {
		$this->Fields();
		return $this->Primaries;
	}
	function getPrimary() {
		$x = $this->getPrimaries();
		return reset($x);
	}
	function getAutoIncrement() {
		$this->Fields();
		return $this->AutoIncrement;
	}
	/* */
	function getStatus() {
		if (!isset($this->aStatus)) {
			$this->aStatus = $this->Db->row("SHOW TABLE STATUS LIKE '".$this."' ");
		}
		return $this->aStatus;
	}
	function getNextId() {
		$this->getStatus();
		return $this->aStatus['Auto_increment'];
	}
	/* */
	function entryId($vs) {
		if (!is_array($vs)) return $vs;
		$this->Fields();
		$part = [];
		foreach ($this->Primaries AS $primary => $Obj) {
			if (!isset( $vs[$primary] )) return false;
			$part[] = $vs[$primary];
		}
		return implode('-:-', $part);
	}
	function entryId2Array($id) {
		$this->Fields();
		$arr = [];
		if (is_array($id)) {
			foreach ($this->Primaries AS $primary => $Obj) {
				if (!isset($id[$primary])) return false;
				$arr[$primary] = $id[$primary];
			}
		} else {
			$vs = explode('-:-',$id);
			foreach ($this->Primaries AS $primary => $Obj)
				$arr[$primary] = array_shift($vs);
		}
		return $arr;
	}
	function entryId2where($id, $tAlias = null) {  // values2Where ?
      	$array = $this->entryId2Array($id);
      	if (!$array) return false;
		foreach ($array as $n => $v)
			$wheres[] = !$tAlias ? " `".$n."` = ".$this->Db->quote($v) : " ".$tAlias.'.'.$n." = ".$this->Db->quote($v);
		return implode(' AND ', $wheres);
	}

	function selectByID($id) {
		foreach ($this->select($this->entryId2where($id)) as $vs)
			return $vs;
	}
	function select($v = '1') {
		$return = [];
		foreach ($this->Db->all("SELECT * FROM ".$this." WHERE ".$v) as $entry) {
			$return[$this->entryId($entry)] = $entry;
		}
		return $return;
	}
	function valuesToSqls($values) {
		$sqls = [];
		foreach ($this->Fields() AS $field => $Field) {
			if (!array_key_exists($field, $values)) continue;
			$sqls[] = " `".$field."` = ".$Field->valueToSql($values[$field])." ";
		}
		return $sqls;
	}
	function valuesToWhere($values) {
		return implode(' AND ',$this->valuesToSqls($values));
	}
	function valuesToSet($values) {
		return implode(', ',$this->valuesToSqls($values));
	}

	function insert($values = []) {
		$set = $this->valuesToSet($values);

		qg::fire('dbTable::insert-before', ['Table' => $this, 'data' => &$values, 'return' => &$return]);
		if ($return !== null) return $return;

		$Statement = $this->Db->query("INSERT INTO ".$this.($set ? " SET ".$set : " () values () "));
		if (!$Statement || !$Statement->rowCount()) return false;

		if ($auto = (string)$this->getAutoIncrement()) {
			$values[$auto] = $this->Db->lastInsertId();
		}

		$id = $this->entryId($values);

		qg::fire('dbTable::insert-after', ['Table' => $this, 'id' => $id, 'data' => &$values]);
		return $id;
	}
	function update($id, $values = null) {
		if ($values===null) {
			$values = $id;
			$id = $this->entryId($values);
		}
		$set = $this->valuesToSet($values);
		if ($set) {

			qg::fire('dbTable::update-before', ['Table' => $this, 'id' => $id, 'data' => &$values, 'return' => &$return]);
			if ($return !== null) return $return;

			$Statement = $this->Db->query("UPDATE ".$this." SET ".$set." WHERE ".$this->entryId2where($id));
			if (!$Statement->rowCount()) return $id;

			qg::fire('dbTable::update-after', ['Table' => $this, 'id' => $id, 'data' => &$values]);

			return $id;
		}
	}
	function ensure($values = []) {
		$set = $this->valuesToSet($values);
		$where = $this->entryId2where($values); // use $this->valuesToWhere todo ???
		if ($where && $this->Db->row("SELECT * FROM ".table($this)." WHERE ".$where))
			return $this->update($values);
		else
			return $this->insert($values);
	}

	function delete($id) {
		$id = $this->entryId($id);
		$values = $this->entryId2Array($id);

		qg::fire('dbTable::delete-before', ['Table' => $this, 'data' => &$values, 'id' => &$id, 'return' => &$return]);
		if ($return !== null) return $return;

		$Statement = $this->Db->query("DELETE FROM ".$this." WHERE ".$this->entryId2where($id));
		if (!$Statement->rowCount()) return;
		qg::fire('dbTable::delete-after', ['Table' => $this, 'data' => &$values, 'id' => $id]);

		// todo, test, not very good implementation
		foreach ($this->Children() as $Field) {
			switch ($Field->vs['on_parent_delete']) {
				case 'cascade':
					foreach ($this->Db->query("SELECT * FROM ".$Field->Table." WHERE ".$Field." = ".$this->Db->quote($id)) as $row) {
						$Field->Table->delete($row);
					}
				break;
				case 'setnull':
					//$F->updateIf($E, null);	// todo
				break;
			}
		}
		return true;
	}

	/* relations */
	function Children() {
		if (!isset($this->Children)) {
			$this->Children = [];
			$sql = "SELECT * FROM qg_db_field WHERE parent = ".$this->Db->quote($this);
			foreach ($this->Db->query($sql) as $vs)
				$this->Children[] = $this->Db->{$vs['tab']}->{$vs['name']};
		}
		return $this->Children;
	}

	function __toString() { return $this->_name; }
	function name()       { return $this->_name; }

	/* export */
	function exportXmlEntry($vs, $basis) {
		$str = '<entry table="'.$this.'">';
		foreach ($this->Fields() AS $name => $F) {
			if (!isset($vs[$name])) continue;
			if ($F->isPrimary() && $F->isAutoIncrement()) continue;
			$str .= '<field name="'.$name.'" value="'.htmlspecialchars($vs[$name]).'"></field>';
		}
		foreach ($this->Children() as $CF) {
			if ($CF->vs['on_parent_copy'] !== 'cascade') continue;
			$str .= $CF->Table->exportXml($CF, $basis);
		}
		return $str .= '</entry>';
	}

	function exportXml($name, $value) {
		$name = (string)$name;
		$str = '';
		foreach ($this->Db->query("SELECT * FROM ".table($this)." WHERE ".$name." = ".$this->$name->valueToSql($value)) as $row) { // table function used!
			$basis = $row[$name];
			unset($row[$name]);
			$str .= $this->exportXmlEntry($row, $basis);
		}
		return $str;
	}

	/* import */
	function _importXmlNode($nodes, $Parent=null, $parentValue=null) {
		foreach ($nodes AS $node) {
			if ($node->nodeName !== 'field') continue;
			$name  = $node->getAttributeNode('name')->nodeValue;
			$value = $node->getAttributeNode('value')?$node->getAttributeNode('value')->nodeValue:'';
			$vs[$name] = html_entity_decode($value);
		}
		foreach ($this->Fields() as $F) { // set parent-value
			if ($F->vs['on_parent_copy'] !== 'cascade') continue;
			if ($F->Parent() && $F->Parent() !== $Parent) continue;
			$vs[(string)$F] = $parentValue;
		}
		$id = $this->insert($vs);
		foreach ($nodes AS $node) {
			if ($node->nodeName !== 'entry') continue;
			$table  = $node->getAttributeNode('table')->nodeValue;
			$this->Db->$table->_importXmlNode($node->childNodes, $this, $id);
		}
		return $id;
	}

	function copy($name, $value) {
		if ($xml = $this->exportXml($name, $value)) return $this->Db->importXml($xml);
		return false;
	}

	/* Entry */
	function Entry($id = null) {
		static $cache = [];
		$t = (string)$this;
		$Cl = 'qg\dbEntry_'.$t;
		$Cl = class_exists($Cl, false) ? $Cl : 'qg\dbEntry';
		if ($id instanceof $Cl) return $id; // performance
		if (func_num_args() === 0) {
			$Entry = new $Cl($this);
			$id = (string)$Entry;
			$cache[$t][$id] = $Entry;
		} else {
			$values = is_array($id) ? $id : $this->entryId2Array($id);
			$id     = is_array($id) ? $this->entryId($id) : (string)$id;
			$cache[$t][$id] ?? ($cache[$t][$id] = new $Cl($this, $values));
			// if (!isset($cache[$t][$id])) { zzz
			// 	$cache[$t][$id] = new $Cl($this, $values);
			// }
		}
		return $cache[$t][$id];
	}
	function selectEntries($str = '') {
		$Es = [];
		foreach ($this->Db->query("SELECT * FROM ".$this." ".$str) as $row) {
			$Entry = $this->Entry($row);
			$Es[(string)$Entry] = $Entry;
		}
		return $Es;
	}

	// ALTER
	function addField($data) {
		if (!is_array($data)) $data = ['name'=>$data];
		$data = $data + ['type'=>'varchar','lenght'=>255,'null'=>false];
		$sql = "ALTER TABLE ".$this." ADD ".$data['name']." ".db::_array_to_column_definition($data);
		$this->Db->query($sql);
		$this->Db->query("INSERT IGNORE INTO qg_db_field SET tab = '".$this."', name = ".D()->quote($data['name']));
		$this->Fs = null;
		$this->fieldsCache->remove();
		return $this->{$data['name']};
	}
	function remField($name) {
		$this->Db->query("DELETE FROM qg_db_field WHERE tab = '".$this."' AND name = ".D()->quote($name));
		$this->Db->query("ALTER TABLE ".$this." DROP ".$name." ");
		$this->fieldsCache->remove();
		unset($this->Fs[$name]);
		//$this->Fs = null;
	}
}
