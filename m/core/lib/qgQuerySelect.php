<?php
namespace qg;

class qgQuerySelect {

	public  $R  = null;
	public 	$Field = null;
	public 	$updatable;

	function __construct($R, $select, $alias = null, $From = null) {
		$this->R = $R;
		$this->alias = (string)($alias ?: (isset($this->R->Selects[$select]) ? $this->R->nextSelectAlias() : $select));
		//$this->alias = $this->R->nextSelectAlias(); // smaller, better?
		$this->select = $select;
		$this->From = $From;
		if ($this->From) {
			$this->Field = $this->From->Table->{$this->select};
			$this->From->Selects[$this->alias] = $this;
		}
	}
	function __toString() { return $this->alias; }
	function join( $alias = null) {
		$Table = $this->Field->Parent();
		$this->RelFrom = $this->R->leftJoin($Table, null, $alias);
		$this->RelFrom->addOn($this->From.'.'.$this->Field.' = '.$this->RelFrom.'.'.$Table->getPrimary());
		return $this->RelFrom;
	}
	function formel() {
		return $this->Field ?  $this->From.'.'.$this->Field : $this->select;
	}
	public $insertValue = false;
	function setInsertValue($value) {
		$this->insertValue = $value;
	}
	function getInsertValue() {
		return $this->insertValue;
	}
	public $updateValue = false;
	function setUpdateValue($value) {
		$this->updateValue = $value;
	}
	function getUpdateValue() {
		return $this->updateValue;
	}
	function setFix($value) {
		$this->R->addWhere($this->formel()." = '".$value."' ");
		$this->setInsertValue($value);
		$this->setUpdateValue($value);
	}
}
