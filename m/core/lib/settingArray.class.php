<?php
namespace qg;

class settingArray implements \ArrayAccess, \Iterator, \countable {
	static public $All = [];
	public  $i = 0;
	public  $k = null;
	public  $w = null;
	public  $b = null;
	private $d = null;
	private $t = null;
	private $sub = null;
	private $hasAll = false;

	static public function getSetting($id=0, $vs=null) {
		$id = (int)(string)$id;
		!isset(self::$All[$id]) && new settingArray($id, $vs);
		return self::$All[$id];
	}
	public function __construct($id=0, $vs=null) {
		self::$All[$id] = $this;
		if (!$id) return;

		if ($vs === null) $vs = D()->row("SELECT * FROM ".table('qg_setting')." WHERE id = ".$id);

		qg::fire('setting::construct', ['Setting'=>$this, 'id'=>$id, 'vs'=>&$vs]);

		if (!$vs) { self::$All[$id] = false; return; }

		$this->i = $vs['id'];
		$this->b = $vs['basis'];
		$this->k = $vs['offset'];
		$this->d = $this->v = $vs['value'];
		$this->w = $vs['w'];
		$this->t = (int)$vs['type'];
		$this->handler = $vs['handler'];
		$this->opts    = $vs['options'];

		if ($vs['w']) {
			if (!Usr()->is()) {
				$vs['usr_value'] = isset($_SESSION['qg_setting']) && array_key_exists($this->i, $_SESSION['qg_setting']) ? $_SESSION['qg_setting'][$this->i] : null;
			} else {
				$row = D()->row("SELECT value FROM qg_setting_usr WHERE setting_id = ".$this->i." AND usr_id = ".Usr()); // bad: D()->one returns false if not found
				$vs['usr_value'] = $row ? $row['value'] : null;
			}
			if ($vs['usr_value'] !== null) {
				$this->v = $vs['usr_value'];
			}
		}
		$this->Cache = cache('qg_setting::basis-offset', $this->b, $this->k);
	}
	public function __toString(){           return (string)$this->v; }
	public function value() {               return (string)$this->v; }
	public function offsetExists($offset) { return true; }
	public function count() {               return count($this->getAll()); }

	public function &getAll(){
		if (!$this->hasAll) {
			$this->sub = [];
			qg::fire('setting::getAll-before', ['Setting'=>$this]);
			foreach (D()->query("SELECT * FROM ".table('qg_setting')." WHERE basis = ".$this->i." ORDER by offset") as $vs) {
				$this->sub[$vs['offset']] = self::getSetting($vs['id'], $vs);
			}
			qg::fire('setting::getAll-after', ['Setting'=>$this]);
			$this->hasAll = true;
		}
		return $this->sub;
	}
	public function has($offset) {
		$offset = (string)$offset;
		if (!isset($this->sub[$offset])) {
			if ($this->hasAll) return false;
			// todo?: runtime cache for "has nots", und berücksichtige diese bei offset create;
			$Cache = cache('qg_setting::basis-offset', $this->i, $offset);
			if (!$Cache->get($vs)) {
				qg::fire('setting::getOffset-before', ['Setting'=>$this]);
				$vs = D()->row("SELECT * FROM ".table('qg_setting')." WHERE basis = ".$this->i." AND offset = ".D()->quote($offset));
				qg::fire('setting::getOffset-after', ['Setting'=>$this]);
			}
			if (!$vs) return false;
			$this->sub[$offset] = self::getSetting($vs['id'], $vs);
		}
		return $this->sub[$offset];
	}
	public function offsetGet($offset) {
		$offset = (string)$offset;
		if (!isset($this->sub[$offset]) && !$this->has($offset)) {
			//echo 'create setting '.$offset; // problem dont create offset if vers::$log;
			//if (D()->row("SELECT * FROM ".table('qg_setting')." WHERE basis = ".$this->i." AND offset = ".D()->quote($offset))) { trigger_error('qg_setting '.$this->i.'::'.$offset.' exists!'); exit(); }
			$id = D()->qg_setting->insert(['basis'=>$this->i, 'offset'=>$offset]);
			unset(self::$All[$id]); // can exist as "false"
			$this->hasAll = false;
			cache('qg_setting::basis-offset', $this->i, $offset)->remove();
			$this->has($offset);
		}
		return $this->sub[$offset];
	}
	public function getDefault() { return $this->d; }
	public function setDefault($v) {
		$vs = [];
		if ($v instanceof settingArray) {
			foreach ($v as $offset => $val) $this[$offset]->setDefault($val);
			$this->setDefault((string)$v);
			$this->setType($v->getType());
			$this->setHandler($v->getHandler());
			call_user_func_array([$this, 'setOptions'], (array)$v->getOptions());
		} elseif (is_array($v)) {
			foreach ($v as $offset => $val) {
				$this[$offset]->setDefault($val);
			}
		} elseif ((string)$this->d !== (string)$v) {
			$vs['value'] = $v;
			$this->d = $v;
			if (!$this->w) $this->v = $v;
		}
		$this->sub = null;
		$this->hasAll = false;
		if (!count($vs)) return $this; // return $this needed?
		$this->Cache->remove();
		D()->qg_setting->update($this->i, $vs);
		return $this; // return $this needed?
	}
	public function setUser($v) {
		if (is_array($v)) {
			foreach ($v as $offset => $val) {
				$this->has($offset) && $this[$offset]->setUser($val);
			}
			return;
		}
		if (!$this->w) return;
		if ((string)$this === (string)$v) return;
		if (!Usr()->is()) {
			$_SESSION['qg_setting'][$this->i] = $v;
		} else {
			$sql = " REPLACE INTO qg_setting_usr SET 	" .
				   "   setting_id = ".$this->i.",		" .
				   "   usr_id     = ".Usr().",			" .
				   "   value      = ".D()->quote($v)."	";
			D()->query($sql);
		}
		$this->v = $v;
		return true;
	}
	public function offsetSet($offset, $v) {
		$this[$offset]->setDefault($v);
	}
	public function offsetUnset($offset) {
		$this->getAll();
		if (!isset($this->sub[$offset])) return;
		$SET = $this->sub[$offset];
		$arr = [];
		foreach ($SET as $ind => $egal) $arr[] = $ind;
		foreach ($arr as $ind) unset($SET[$ind]);
		D()->qg_setting->delete($SET->i);
		$SET->Cache->remove();
		unset($this->sub[$offset]);
 	}
	public function rewind()  { $this->getAll(); reset($this->sub); }
	public function current() { return current($this->sub); }
	public function key()     { return key($this->sub); }
	public function next()    { return next($this->sub); }
	public function valid()   { return (current($this->sub) !== false); }
	public function id()      { return $this->i; trigger_error('used?'); }
	public function offset()  { return $this->k; }
	public function getType() {
		$typeToString = [0=>'string', 1=>'bool', 2=>'float', 3=>'int'];
		return $typeToString[$this->t];
	}
	public function setType($type) {
		$stringToType = ['string'=>0, 'bool'=>1, 'float'=>2, 'int'=>3];
		$t = $stringToType[$type];
		if ($this->t === $t) return $this;
		$this->Cache->remove();
		D()->qg_setting->update(['id'=>$this->i, 'type'=>$t]);
		$this->t = $t;
		return $this;
	}
	public function make($index, $value) {
		if (!$this->has($index)) $this[$index] = $value;
		return $this[$index];
	}
	public function custom($v = 1) {
		$v = (int)(bool)$v;
		if ((int)$this->w === $v) return $this;
		$this->Cache->remove();
		D()->qg_setting->update(['id'=>$this->i, 'w'=>$v]);
		$this->w = $v;
		return $this;
	}
	public function get() {
		if ($this->getAll()) {
			foreach ($this->sub as $i => $S) {
				!$S && trigger_error('setting "'.$i.'" in ->sub but not exists');
				$array[$i] = $S->get();
			}
			return $array;
		}
		switch ($this->getType()) {
			case 'bool':  return (bool)$this->v;
			case 'float': return (float)$this->v;
			case 'int':   return (int)$this->v;
		}
		return $this->v;
	}
	public function getJson() {
		return json_encode($this->get());
	}
	public function setHandler($v) {
		$v = (string)$v;
		if ($this->handler === $v) return $this;
		$this->Cache->remove();
		D()->qg_setting->update(['id'=>$this->i, 'handler'=>$v]);
		$this->handler = $v;
		return $this;
	}
	public function getHandler() {
		if (!$this->handler) {
			switch($this->t){
				case 1: return 'checkbox';
				case 2: case 3: return 'number';
			}
		}
		return $this->handler;
	}
	public function setOptions() {
		$str = json_encode(func_get_args());
		if ($this->opts === $str) return $this;
		$this->Cache->remove();
		D()->qg_setting->update(['id'=>$this->i, 'options'=>$str]);
		$this->opts = $str;
		return $this;
	}
	public function getOptions() {
		return json_decode($this->opts,1);
	}
	public function Parent() {
		return settingArray::getSetting($this->b);
	}
	public function in(settingArray $In) {
		$S = $this;
		while ($S && $S->i) {
			if ($In->i == $S->i) return true;
			$S = $S->Parent();
		}
	}
}

function serverInterface_Setting($value, $path=0, $startId=0) {
	if (!is_array($path)) {
		$startId = $path;
		$path = [];
	}
	$S = $startId ? settingArray::getSetting( (int)$startId ) : G()->SET;
	foreach ($path as $key) {
		if (!$S->has($key)) return false;
		$S = $S[$key];
	}
	$S->setUser($value);
	return (bool)$S->w;
}
