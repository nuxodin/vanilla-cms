<?php
namespace qg;

class qgQueryEntry implements \ArrayAccess, \Iterator {

	public function __construct($Query, $id, $vs) {
		$this->Q  = $Query;
		$this->id = $id;
		foreach ($vs as $n => $v)
			$this->vs[$n] = new qgQueryCell($this, $n, $v);
	}
	function __toString() {
		return $this->id;
	}
	/*
	protected $vs;
	function __get($n) { // todo?
	return $this->vs[$n] ?? null;
	}
	*/
	public function offsetExists($id)  { return isset($this->vs[$id]); }
	public function offsetGet($id)     { return $this->vs[$id]; }
	public function offsetSet($id, $v) { return $this->vs[$id]->update($v); }
	public function offsetUnset($id)   { }

	public function rewind()  { reset($this->vs); }
	public function current() { return current($this->vs); }
	public function key()     { return key($this->vs); }
	public function next()    { return next($this->vs); }
	public function valid()   { return $this->current() !== false; }

	function update($vs) {
		foreach ($this->Q->Selects AS $alias => $Select) {
			if (isset($vs[$alias])) $sets[] = " ".$Select->formel()." = '".$vs[$alias]."' \n";
		}
		if (isset($sets)) {
			$sql =
			" UPDATE  \n\t ".$this->Q->getSqlFrom()." 									\n".
			" SET \n\t".implode("\n\t , ", $sets)." 									\n".
			" WHERE ".$this->Q->Table->entryId2where($this->id, $this->Q->From->alias)."\n";
			$this->Q->Db->query($sql);
			return true;
		}
	}
}

class qgQueryCell {
	function __construct($Entry, $n, $v) {
		$this->Entry = $Entry;
		$this->name = $n;
		$this->value = $v;
		$this->Select = $this->Entry->Q->Selects[$n];
	}
	function __toString() {
		return (string)$this->value;
	}
	function update($v) {
		if ($ret = $this->Entry->update([$this->name=>$v])) {
			$this->value = $v;
			return  $ret;
		}
	}
}
