<?php
namespace qg;

class L {
	static $def = 'en';
	static $now = 'en';
	static $usr = 'en';
	static $all = [];
	static $nsPath = [];
	static $ns = '';

	static public function init() {
		self::$usr = Usr()->is()
			? Usr()->lang
			: ($_SESSION['qg']['lang'] ?? '');

		if (isset($_GET['changeLanguage'])) {
			self::$usr = $_GET['changeLanguage'];
		} elseif (preg_match('/^([a-z][a-z])\//', appRequestUri, $match)) {
			self::$usr = $match[1];
		}
		if (!isset(self::$all[self::$usr])) {
			self::$usr = '';
		}
		if (self::$usr === '') {
			self::$usr = self::_frombrowser();
		}
		if (Usr()->is()) Usr()->lang = self::$usr;
		else $_SESSION['qg']['lang'] = self::$usr;
		self::$now = self::$usr;
	}
	static function all() {
		return self::$all;
	}
	static function _fromBrowser() {
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) return self::$def;
		$accepted_languages = preg_split('/,\s*/', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$current_lang = self::$def;
		$current_q = 0;
		foreach ($accepted_languages as $accepted_language) {
			$res = preg_match('/^([a-z]{1,8}(?:-[a-z]{1,8})*)' .
			'(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accepted_language, $matches);
			if (!$res) continue;
			$lang_code = explode('-', $matches[1]);
			$lang_quality = (float)($matches[2] ?? 1);
			while (count($lang_code)) {
				if (in_array(strtolower(join('-', $lang_code)), self::$all)) {
					if ($lang_quality > $current_q) {
						$current_lang = strtolower(join('-', $lang_code));
						$current_q = $lang_quality;
						break;
					}
				}
				array_pop($lang_code);
			}
		}
		return $current_lang;
	}

	static function nsStart($ns) {
		array_push(self::$nsPath, self::$ns);
		self::$ns = $ns;
		L::$now = self::_nsLang(self::$ns);
		//self::_nsLang();
	}
	static function nsStop() {
		self::$ns = array_pop(self::$nsPath);
		L::$now = self::_nsLang(self::$ns);
		//self::_nsLang();
	}
	static function _nsLang($ns) {
		if ($ns) {
			$nsLang = Usr()->is()
				? G()->SET['qg']['lang_ns'][$ns]->custom()->v
				: $_SESSION['qg']['lang_ns'][$ns];
			return $nsLang ?: L::$usr;
		}
		return L::$usr;
	}
	/*
	static function _nsLang(){
		if (self::$ns) {
			$nsLang = Usr()->is()
				? G()->SET['qg']['lang_ns'][self::$ns]->custom()->v
				: $_SESSION['qg']['lang_ns'][self::$ns];
			L::$now = $nsLang ?: L::$usr;
		} else {
			L::$now = L::$usr;
		}
	}
	*/
	static function _addLanguage($l) {
		!D()->smalltext->$l && $l && D()->smalltext->addField($l)->setType('text');
	}
	static function &_getTxts($ns, $l) {
		static $txts = [];
		if (!isset($txts[$l][$ns])) {
			$start = microtime(1);
			self::_addLanguage($l);
			foreach (D()->indexCol("SELECT hash, `".$l."` as txt FROM smalltext WHERE namespace = '".$ns."'") AS $hash => $txt) {
				$txts[$l][$ns][$hash] = $txt;
			}
		}
		return $txts[$l][$ns];
	}
	static function &_getTxt($string) {
		$hash = md5($string);
		$ns = self::$ns;
		$l  = L();
		$txts =& L::_getTxts($ns,$l);
	 	if (!isset($txts[$hash])) {
			D()->query("INSERT INTO smalltext SET namespace='".$ns."', hash = '".$hash."', de = ".D()->quote($string).", original=".D()->quote($string)." "); // todo:change to default:en
			$txts[$hash] = $string;
		}
		G()->SET['qg']['smalltexts_counter']->v && D()->query("UPDATE smalltext SET count = count+1 WHERE hash = '".$hash."'");
		if (!$txts[$hash]) $txts[$hash] = (debug ? '*'.$string.'*' : $string);
		return $txts[$hash];
	}

	static function export($lang, $ns) {
		$txts = [];
		$x = D()->indexCol("SELECT original, ".$lang." FROM smalltext WHERE namespace = '".$ns."'");
		return json_encode($x,JSON_PRETTY_PRINT);
	}
	static function import($lang, $ns, $str) {
		self::_addLanguage($lang);
		$txts = (array)json_decode($str,1);
		foreach ($txts as $original => $txt) {
			if (!$txt) continue;
			$hash = md5($original);
			$E = D()->smalltext->Entry(['hash'=>$hash, 'namespace'=>$ns])->makeIfNot();
			$E->original = $original;
			$E->{$lang} = $txt;
		}
	}
}

function L($string = null) {
	if ($string === null) return L::$now ?: L::$def;
	$str = L::_getTxt($string);
	foreach (func_get_args() as $j => $arg) {
		if ($j) $str = str_replace('###'.$j.'###', $arg, $str);
	}
	return $str;
}
