<?php
namespace qg;

class qgQueryFrom {

	public $R;
	public $Table;
	public $Selects; // werden im Konstruktor von dbRule_Select hinzugefügt.
	private $ons = [];

	function __construct($Rule, $table, $on = null, $join = null, $alias = null) {
		$table = (string)$table;
		$this->R = $Rule;
		$this->alias = $alias ?: ( isset($this->R->Froms[$table]) ? $this->R->nextFromAlias() : $table);
		$this->join = $join;
		$on && $this->addOn($on);
		$this->Table = $this->R->Db->$table;
	}
	function __toString() { return $this->alias; }
	function join($table, $field = null, $alias = null) { /*is "$field = null" ok???*/
		$From = $this->R->leftJoin($table, null, $alias);
		$on = $this.'.'.$this->Table->getPrimary().' = '.$From.'.'.$field;
		$From->addOn( $on );
		//$this->R->doGroupBy = true; todo
		return $this->Froms[$alias] = $From;
	}
	function addOn($on) { $this->ons[] = $on; }
	function getOn() { return implode(' AND ', $this->ons); }
	function select($field, $alias = null) {
		$field = (string)$field; // clean acpect
		if (!isset($this->Selects[$field])) {
			$this->Selects[$field] = $this->R->select($field, $alias, $this);
		}
		return $this->Selects[$field];
	}
	function insert(&$vs) {
		foreach ($this->Selects AS $Select) {
			$alias = (string)$Select;
			/* join-insert *
			if (isset($Select->RelFrom) AND !isset($vs[$alias])) {
				$vs[$alias] = $Select->RelFrom->insertEntry($vs);
			}
			/**/
			if (isset( $vs[$alias] )) {
				$array[(string)$Select->Field] = $vs[$alias];
			}
		}
		return $this->Table->insert($array);
	}
}
