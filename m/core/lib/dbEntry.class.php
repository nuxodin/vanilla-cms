<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class dbEntry {
	private $_T;
	public  $_vs = [];
	private $_eid = false;
	private $_changed = false;
	public  $_is   = null; // null = dont know
	public  $_full = false;

	function __construct($T, $vs = null) {
		$this->_T = $T;
		if (is_array($vs)) {
			$this->_eid = $this->_T->entryId($vs);
			$this->_vs  = $vs;
		} elseif (func_num_args() === 1) {
			$this->_eid = $this->_T->insert();
			$this->_is  = true;
			$this->construct();
		} else {
			$this->_eid = (string)$vs;
		}
//$this->_Cache = cache('dbEntry', $this->_T->_name, $this->_eid); // new, todo make if no argument $vs?
	}

//public $_Cache = null; // new

	protected function construct() {} // used?

	function is() {
		if ($this->_is === null) {
			$this->_full !== false && trigger_error('Entry is = false and full = false should not be!'); // zzz
			$this->getVs();
		}
		return $this->_is ? $this : false;
	}
	function __get($n) {
		!$this->_full && $this->getVs();
		return array_key_exists($n, $this->_vs) ? $this->_vs[$n] : $this->_get($n);
	}
	function _get($name){
		$GLOBALS['skip_stacks'] += 2;
		trigger_error('_get "'.$this->_T.'::'.$name.'" not implemented');
		$GLOBALS['skip_stacks'] -= 2;
	}
	function _full() {
		if ($this->_full === false) {
			$notPrimaries = [];
			foreach ($this->_T->Fields() as $field => $Field) {
				if ($Field->isPrimary()) continue;
				$notPrimaries[] = $field;
			}
			if (!count($notPrimaries)) {
				$this->_full = false;
			} else {
				$this->_full = true;
				foreach ($notPrimaries as $field) {
					if (!array_key_exists($field, $this->_vs)) {
						$this->_full = false;
						break;
					}
				}
				if ($this->_full) $this->_is = true; // ok?
			}
		}
		return $this->_full;
	}
	function getVs() {
		if (!$this->_full()) {
//if (!$this->_Cache->get($data)) {
			$data = $this->_T->selectByID($this->_eid);
//}
			$this->_is = (bool)$data;
			if ($data) $this->_vs = $data;
			$this->_full = true;
		}
		return $this->_vs;
	}
	function setVs($vs) {
		$this->getVs(); // neu
		$this->_changed = true;
		$this->_vs = $vs + $this->_vs;
		return $this;
	}
	function complementVs($vs) {
		$this->_vs += $vs;
	}
	function __set($n, $v) {
		$this->getVs();
		if (array_key_exists($n, $this->_vs) && $this->_vs[$n] !== $v) {
			$this->_changed = true;
		}
		$this->_vs[$n] = $v;
	}
	function makeIfNot() {
		if (!$this->is()) {
			$this->_vs = $this->_T->entryId2Array($this->_eid); // needed? ("->is()" makes this...)
			$this->_T->insert($this->_vs);
			$this->_full = false;
			$this->_is = true;
		}
		return $this;
	}
	function delete() {
//$this->_Cache->remove(); // new
		$this->_T->delete($this->_eid);
		$this->_eid = false;
		$this->_vs = [];
		$this->_is = false;
	}
	function save() {
		if ($this->_changed) {
//$this->_Cache->remove(); // new
			$this->_T->update($this->_eid, $this->_vs);
			$this->_changed = false;
		}
	}
	function __destruct() {
		$this->save();
	}
	function __toString() {
		return (string)$this->_eid;
	}
}
