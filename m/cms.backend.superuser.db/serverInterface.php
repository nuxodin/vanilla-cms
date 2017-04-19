<?php
namespace qg;

class serverInterface_dbField {
	static function setParent($t,$f,$p) {
		if (!Usr()->superuser) return false;
		D()->$t->$f->setParent($p);
		return true;
	}
	static function setFront($t,$f,$v) {
		if (!Usr()->superuser) return false;
		D()->query("UPDATE qg_db_field SET dbm_front = ".(int)$v." WHERE tab='".$t."' AND name='".$f."' ");
		return true;
	}
}

class serverInterface_superuser_db {
	static function addTable($n) {
		D()->addTable($n);
		return true;
	}
	static function addField($t, $n) {
		D()->$t->addField($n);
		return true;
	}
	static function fieldSetOnParentDelete($t, $f, $v) {
		D()->$t->$f->setOnParentDelete($v);
		return true;
	}
	static function fieldSetTyp($t, $f, $v) {
		D()->$t->$f->setType($v);
		return true;
	}
	static function fieldSetLength($t, $f, $v) {
		D()->$t->$f->setLength($v);
		return true;
	}
	static function fieldSetSpecial($t, $f, $v) {
		D()->$t->$f->setSpecial($v);
		return true;
	}
	static function fieldSetNull($t, $f, $v) {
		D()->$t->$f->setNull($v);
		return true;
	}
	static function fieldSetDefault($t, $f, $v) {
		D()->$t->$f->setDefault($v);
		return true;
	}
	static function fieldSetAutoincrement($t,$f,$v) {
		D()->$t->$f->setAutoincrement($v);
		return true;
	}
	static function fieldSetKey($t, $f, $v) {
		D()->$t->$f->setKey($v);
		return true;
	}
	static function setData($t, $e, $f, $v) {
		D()->$t->Entry($e)->$f = $v;
		return true;
	}
	static function removeEntry($t, $e) {
		D()->$t->delete($e);
		return true;
	}
	static function onBefore(){
		if (!Usr()->superuser) return false;
	}
}
