<?php
namespace qg;

function TextPro($id) {
	static $All = [];
	if (!isset($All[$id])) $All[$id] = new TextPro($id);
	return $All[$id];
}

class TextPro {
	var $edit = false;
	function __construct($id) {
		$this->id = (int)$id;
	}
	function get($lang = null) {
		static $cache = null;
		$lang = $lang===null ? L() : (string)$lang;
		$id = (string)$this->id;
		if (!isset($cache[$id][$lang])) {
			$cache[$id][$lang] = new TextPro_lang($this, $lang);
		}
		return $cache[$id][$lang];
	}
	function set($v, $lang = null) {
		$this->get($lang)->set($v);
	}
	function __toString() {
		return $this->getTranslated()->get();
	}
	function getTranslated($lang = null) {
		if ($lang === null) $lang = L();
		$T = $this->get($lang);
		if (!$T->get()) {
			foreach (L::all() as $l) {
				if ($l === $lang) continue;
				$T = $this->get($l);
				if ((string)$T) break;
			}
		}
		return $T;
	}
	function copy() {
		$New = self::generate();
		foreach (D()->query("SELECT * FROM ".table('text')." WHERE id = ".$this->id) as $vs) {
			$vs['id'] = $New->id;
			$vs['log_id'] = liveLog::$id;
			D()->one("SELECT id FROM ".table('text')." WHERE id = ".$vs['id']." AND lang = ".D()->quote($vs['lang']))
				? D()->text->update($vs)
				: D()->text->insert($vs);
		}
		return $New;
	}
	static function generate() {
		$id  = D()->text->insert(['lang'=>L()]);
		$ids = D()->text->entryId2Array($id);
		return new TextPro($ids['id']);
	}
}

class TextPro_lang {
	var $Text;
	var $value = null;
	function __construct($Text, $lang) {
		$this->Text = $Text;
		$this->lang = $lang;
	}
	function get() {
		if (!isset($this->value)) {
			$Cache = cache('textpro', $this->lang, $this->Text->id);
			if (!$Cache->get($this->value)) {
				$this->value = D()->one("SELECT text FROM ".table('text')." WHERE id = ".$this->Text->id." AND lang = '".$this->lang."'");
			}
			qg::fire('textpro_lang::get', ['obj'=>$this]);
		}
		return (string)$this->value;
	}
	function set($v) {
		$this->value = null;
		$data = [
			'id'   => $this->Text->id,
			'lang' => $this->lang
		];
		$has = D()->one("SELECT id FROM ".table('text')." WHERE ".D()->text->valuesToWhere($data)); // why not where id = $data['id']?
		$data['text'] = $v;
		return $has ? D()->text->update($data) : D()->text->insert($data);
	}
	function __toString() {
		return $this->get();
	}
}

$removeCache = function($e){
	extract($e, EXTR_REFS);   // Table, id, data
	if ($Table->_name === 'text') {
		cache('textpro', $data['lang'], $data['id'])->remove();
	}
};
qg::on('dbTable::update-after', $removeCache);
qg::on('dbTable::delete-after', $removeCache);
qg::on('dbTable::insert-after', $removeCache);
