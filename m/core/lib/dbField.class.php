<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class dbField {

	static $dateTypes   = ['DATETIME'=>1, 'DATE'=>1, 'TIMESTAMP'=>1];
	static $stringTypes = ['CHAR'=>1, 'VARCHAR'=>1, 'BINARY'=>1, 'VARBINARY'=>1, 'BLOB'=>1, 'TEXT'=>1, 'ENUM'=>1, 'SET'=>1];

	private $type    = null;
	private $length  = null;
	private $special = null;
	private $_name;

	function __construct($Table, $name, $vs) {
		$this->Table = $Table;
		$this->Db    = $Table->Db;
		$this->_name = $name;
		$this->vs    = $vs;
	}
	function __toString() { return $this->_name; }
	function name() { return $this->_name; }

	function valueToSql ($value) {
		$type = strtoupper($this->getType());
		if (is_float($value)) { $value = str_replace(',','.',(string)$value); }
		if ($value === null && $this->getNull()) {
			$valSql = 'NULL';
		} elseif ($value === '' && $this->getNull() && !isset(self::$stringTypes[$type])) {
			$valSql = 'NULL';
		} elseif (is_numeric($value) && isset(self::$dateTypes[$type])) {
			$valSql = "'".Date('Y-m-d H:i:s', $value)."'";
		} else {
			$valSql = $this->Db->quote((string)$value);
		}
		return $valSql;
	}

	private $_Title = null;
	function Title($value = null) {
		if (!$this->vs['title']) {
			$this->vs['title'] = TextPro::generate()->id;
			// todo: set value to table-name
			$this->Db->qg_db_field->update($this->vs);
			$this->Table->fieldsCache->remove();
		}
		if ($this->_Title === null) {
			$this->_Title = TextPro($this->vs['title']);
		}
		$value!==null && $this->_Title->set($value);
		return $this->_Title;
	}

	function isPrimary() {
		return $this->vs['Key'] === 'PRI';
	}
	function getKey() {
		return $this->vs['Key'];
	}
	function isAutoIncrement() {
		return $this->vs['Extra'] === 'auto_increment';
	}
	function explodeTypeData() {
		if ($this->type == null) {
			preg_match('/^([a-z]+)(\(([^)]+)\)|.*)(.*)$/',$this->vs['Type'], $matches);
			$this->type    = strtolower(trim($matches[1]));
			$this->length  = trim($matches[3]);
			$this->special = strtolower(trim($matches[4]));
		}
	}
	function change($data) {
		$data['type']          = $data['type']          ?? $this->getType();
		$data['length']        = $data['length']        ?? $this->getLength();
		$data['special']       = $data['special']       ?? $this->getSpecial();
		$data['null']          = $data['null']          ?? $this->getNull();
		$data['default']       = $data['default']       ?? $this->vs['Default'];
		$data['autoincrement'] = $data['autoincrement'] ?? $this->isAutoIncrement();
		$sql = "ALTER TABLE ".$this->Table." CHANGE ".$this." ".($data['name'] ?? $this)." ".db::_array_to_column_definition($data);
		if (isset($data['after'])) $sql .= $data['after'] ? " AFTER `".$data['after']."`" : " FIRST";
		$this->Db->query($sql);
		$this->Table->fieldsCache->remove();
	}

	// function __get($what){ // todo?
	// 	switch ($what){
	// 		case 'type':
	// 			$this->explodeTypeData();
	// 			return $this->type;
	// 		case 'length':
	// 			$this->explodeTypeData();
	// 			return (string)$this->length;
	// 	}
	// 	trigger_error('getter '.hee($what).' not supported');
	// }

	// deprecated alter
	function getType() {
		$this->explodeTypeData();
		return $this->type;
	}
	function setType($v) {
		$this->change(['type'=>$v]);
		$this->type = $v;
	}
	function getLength() {
		$this->explodeTypeData();
		return (string)$this->length;
	}
	function setLength($v) {
		$this->change(['length'=>$v]);
		$this->length = $v;
	}
	function getSpecial() {
		$this->explodeTypeData();
		return $this->special;
	}
	function setSpecial($v) {
		$this->change(['special'=>$v]);
		$this->special = $v;
	}
	function getNull() {
		return $this->vs['Null'] === 'YES';
	}
	function setNull($v) {
		$v = (bool)$v;
		$this->change(['null'=>$v]);
		$thss->vs['Null'] = $v;
	}
	function getDefault() {
		return $this->vs['Default'];
	}
	function setDefault($v) {
		$this->change(['default'=>$v]);
	}
	function getAutoincrement() {
		return $this->isAutoIncrement()?'true':'false';
	}
	function setAutoincrement($v) {
		$this->change(['autoincrement'=>$v]);
	}
	function getID() {
		return $this->vs['id'];
	}
	function Parent() {
		return $this->Db->{$this->vs['parent']};
	}
	function setParent($p) {
		$this->Db->query("UPDATE qg_db_field SET parent = '".$p."' WHERE tab = '".$this->Table."' AND name = '".$this."'");
		$this->Table->fieldsCache->remove();
		$this->vs['parent'] = $p;
	}
	function setOnParentDelete($v) {
		$this->Db->query("UPDATE qg_db_field SET on_parent_delete = '".$v."' WHERE tab = '".$this->Table."' AND name = '".$this."'");
		$this->Table->fieldsCache->remove();
		$this->vs['on_parent_delete'] = $v;
	}
	function setOnParentCopy($v) {
		$this->Db->query("UPDATE qg_db_field SET on_parent_copy = '".$v."' WHERE tab = '".$this->Table."' AND name = '".$this."'");
		$this->Table->fieldsCache->remove();
		$this->vs['on_parent_copy'] = $v;
	}
	function setAfter($F) {
		$this->change(['after'=>$F]);
		$this->Table->Fs = null;
		$this->Table->fieldsCache->remove();
	}
	function setKey($type) {
		$type = strtoupper($type);
		if ($this->getKey() === $type) return;

		$this->Table->fieldsCache->remove();

		if ($type === 'PRI') {
			$this->_setPrimary(1);
		} else {
			$this->_setPrimary(0);
			if ($this->getKey()) {
				$this->Db->query("ALTER TABLE ".$this->Table." DROP INDEX ".$this);
			}
			if ($type === 'MUL') {
				if (in_array( $this->getType(), ['text','tinytext','mediumtext','longtext'])) {
					$this->Db->query("ALTER TABLE ".$this->Table." ADD FULLTEXT (".$this.")");
				} else {
					$this->Db->query("ALTER TABLE ".$this->Table." ADD INDEX (".$this.")");
				}
			} elseif ($type === 'UNI') {
				$this->Db->query("ALTER TABLE ".$this->Table." ADD UNIQUE (".$this.")");
			}
		}
	}
	function _setPrimary($v) {
		if ($this->isPrimary() == $v) return;
		$ps = [];
		foreach ($this->Table->getPrimaries() as $field => $Field) {
			$ps[$field] = $field;
			if ($Field->isAutoIncrement()) {
				$Auto = $Field;
				$Field->setAutoincrement(0);
			}
		}
		if ($ps) {
			$this->Db->query("ALTER TABLE ".$this->Table." DROP PRIMARY KEY");
			if (count($ps) > 1) {
				foreach ($ps as $p) $this->Db->query("ALTER TABLE ".$this->Table." DROP INDEX ".$p);
			}
		}
		if ($v) {
			$ps[(string)$this] = (string)$this;
			$this->vs['Key'] = 'PRI';
		} else {
			unset($ps[(string)$this]);
			$this->vs['Key'] = '';
		}
		if ($ps) {
			$this->Db->query("ALTER TABLE ".$this->Table." ADD PRIMARY KEY (".implode(',',$ps).")");
			if (count($ps) > 1) {
				foreach ($ps as $p) $this->Db->query("ALTER TABLE ".$this->Table." ADD INDEX (".$p.")");
			}
		}
		isset($Auto) && $Auto->setAutoincrement(1);
	}
	// deprecated (leave in v5 for update-compatibility?)
	function getTyp(){ trigger_error('deprecated'); return $this->getType(); }
	function setTyp($v) { trigger_error('deprecated'); return $this->setType($v); }
}
