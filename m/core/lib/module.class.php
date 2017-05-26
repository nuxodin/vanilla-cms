<?php
namespace qg;

class dbEntry_module extends dbEntry {
	function Title(){
		if (!$this->title_id) $this->title_id = TextPro::generate()->id;
		$T = TextPro($this->title_id);
		!trim($T) && $T->set($this->name);
		return $T;
	}
	function path() { return sysPATH.$this->name.'/'; }
}

class module {
	static function all() {
		return D()->module->selectEntries("ORDER BY name");
	}
	static function syncLocal() {
		// neu
		$file = sysPATH.'module.json'; // neu
		touch($file); // neu
	 	$data = json_decode(file_get_contents($file), true) ?: []; // neu
		foreach ($data as &$module) $module['local_time'] = ''; // neu

		// old
		D()->query("UPDATE module SET local_time = 0"); // old
		foreach (self::all() as $M) $M->local_time = ''; // old

		$d = opendir(sysPATH);
		$module_changed = 0;
		while ($m = readdir($d)) {
			if ($m[0] === '.' || !is_dir(sysPATH.$m)) continue;

			// old
			$M = D()->module->Entry($m)->makeIfNot();
			$M->local_time = dir_mtime(sysPATH.$M->name);
			$M->local_version = $M->local_version ?: '0.0.0';
			if ($module_changed < $M->local_time) $module_changed = $M->local_time;
			$M->save();

			// neu
			$module =& $data[$m];
			$module['local_time']    = $M->local_time; // zzz
			$module['local_version'] = $M->local_version ?: '0.0.0'; // zzz
			//$module['local_time'] = dir_mtime(sysPATH.$m); // todo
			//$M['local_version']   = $M['local_version'] ?: '0.0.0'; // todo
			if ($module_changed < $module['local_time']) $module_changed = $module['local_time'];
		}
		G()->SET['qg']['module_changed'] = $module_changed;

		// old
		foreach (self::all() as $M) if (!$M->local_time) $M->local_version = '';

		// neu
		foreach ($data as $name => $module) if (!$module['local_time']) unset($data[$name]);
		file_put_contents($file, json_encode($data,JSON_PRETTY_PRINT));
	}
	static function syncRemote() { // deprecated
		//trigger_error('deprecated');
		foreach (self::all() as $M) {
			//$M->server = ''; // there are other servers...
			$M->server_version = '';
			$M->server_time = '';
			$M->server_size = '';
		}
		foreach (qg::Store()->indexAll() as $name => $vs) {
			$E = D()->module->Entry($name)->makeIfNot();
			$E->server         = qg::Store()->host;
			$E->server_version = $vs['version'];
			$E->server_time    = $vs['time'];
			$E->server_size    = $vs['size'];
			$E->save();
		}
	}
	static function sync() { // deprecated
		//trigger_error('deprecated');
		//D()->query("UPDATE module SET local_time = 0, server_version = '', server_time = ''"); // why needed?
		self::syncLocal();
		self::syncRemote();
		D()->query("DELETE FROM module WHERE server_time = 0 && local_time = 0");
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
