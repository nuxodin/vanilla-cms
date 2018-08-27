<?php
namespace qg;

class dbEntry_module extends dbEntry {
	function Title(){
		if (!$this->title_id) $this->title_id = TextPro::generate()->id;
		$T = TextPro($this->title_id);
		!trim($T) && $T->set($this->name);
		return $T;
	}
}

class module {
	private static $_index = null;
	static function &index() {
		if (self::$_index === null) {
			$file = sysPATH.'index.json';
			if (!is_file($file)) touch($file); // used before install core
			self::$_index = json_decode(file_get_contents(sysPATH.'index.json'), true);
		}
		return self::$_index;
	}
	static function saveIndex(){
		file_put_contents(sysPATH.'index.json', json_encode(self::$_index,JSON_PRETTY_PRINT));
	}
	static function syncLocal() {
		$data = &module::index();
		foreach ($data as &$module) $module['changed'] = '';
		$maxChanged = 0;
		foreach (scandir(sysPATH) as $m) {
			if ($m[0] === '.' || !is_dir(sysPATH.$m)) continue;
			$module =& $data[$m];
			$module['changed'] = dir_mtime(sysPATH.$m);
			$module['version'] = $module['version'] ?? '0.0.0';
			$module['server']  = $module['server']  ?? '';
			$module['updated'] = $module['updated'] ?? 0;
			if ($maxChanged < $module['changed']) $maxChanged = $module['changed'];
			D()->module->Entry($m)->makeIfNot();
		}
		G()->SET['qg']['module_changed'] = $maxChanged;
		foreach ($data as $name => &$module) if (!$module['changed']) unset($data[$name]);
		module::saveIndex();
		// cleanup installed-data
		foreach (qg::getInstalledData() as $name => $version) {
			!isset($data[$name]) && qg::setInstalled($name, false);
		}
		// cleanup db
		foreach (D()->col("SELECT name FROM module") as $name) {
			!isset($data[$name]) && D()->query("DELETE FROM module WHERE name = ".D()->quote($name));
		}
	}
	static function delete($module) {
		rrmdir(sysPATH.$module);
        D()->query("DELETE FROM module WHERE name = ".D()->quote($module));
        qg::setInstalled($module, false);
	}
}

function versionIsSmaller($v1, $v2, $limit=6) {
	$v1 = explode('.', $v1);
	$v2 = explode('.', $v2);
	for ($i=0;$i<$limit;$i++) {
		if (!isset($v1[$i])) $v1[$i] = 0;
		if (!isset($v2[$i])) $v2[$i] = 0;
		if ($v2[$i] == $v1[$i]) continue;
		return $v2[$i] > $v1[$i];
	}
	return false;
}
function dir_mtime($path){
	$time = 0;
	$dir = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), true);
	try {
		foreach ($dir as $file) {
			if (is_dir($file)) continue;
			$time = max($time, filemtime($file));
		}
	} catch (\Exception $e) { }
	return $time;
}
