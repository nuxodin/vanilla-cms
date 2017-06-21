<?php
namespace qg;

class qg {
	// init
	static public $modules = [];
	static function need($mName) {
		if (isset(self::$modules[$mName])) return;
		$path = sysPATH.$mName.'/';
		if (!file_exists($path) && !self::Store()->download($mName)) {
			$GLOBALS['skip_stacks'] += 1; trigger_error($mName.' can not be downloaded'); $GLOBALS['skip_stacks'] -= 1;
			return;
		}
		!file_exists($path) && self::Store()->download($mName);
		is_file($path.'qg.php') && require_once $path.'qg.php';
		!self::initialized($mName) && self::initialize($mName);
		self::$modules[$mName] = 1;
	}
	static function init() {
		foreach (scandir(sysPATH) as $name) {
			if ($name[0] === '.') continue;
			if (isset(self::$modules[$name])) continue;
			if (!is_dir(sysPATH.$name)) continue;
			self::need($name);
		}
		self::fire('action');
	}
	// install
	static function initialize($module) {
		time_limit(600);
		$_x_module = $module;
		$path = sysPATH.$module.'/';
		is_file($path.'/dbscheme.xml') && dbScheme::check(file_get_contents($path.'dbscheme.xml'));
		is_file($path.'install.php')   && require_once($path.'install.php');
		self::setInstalled($_x_module);
	}
	/* initialized (no new local version detected) */
	private static function initialized(&$m) {
		self::$installedData === null && self::getInstalledData();
		return isset(self::$installedData[$m]) && self::$installedData[$m] === module::index()[$m]['version'];
	}

	static $installedData = null;
	static function setInstalled($m, $do = true) {
		$data = &self::getInstalledData();
		if ($do) {
			//$version = &module::index()[$m]['version'] ?? '0.0.0';
			$moduleIndex = &module::index();
			if (!isset($moduleIndex[$m])) {
				$moduleIndex[$m]['version'] = '0.0.0';
				module::saveIndex();
			}
			$data[$m] = $moduleIndex[$m]['version'];
		} else unset($data[$m]);
		$file = appPATH.'qg/module_installed.json';
		//!is_dir(appPATH.'qg') && mkdir(appPATH.'qg');
		file_put_contents($file, json_encode($data,JSON_PRETTY_PRINT));
	}
	static function &getInstalledData() {
		if (self::$installedData === null) {
			$file = appPATH.'qg/module_installed.json';
			if (is_file($file)) self::$installedData = json_decode(file_get_contents($file), true);
		}
		return self::$installedData;
	}

	static function Store(){
		static $Store = null;
		if (!$Store) {
			$user = defined('qg_user')?qg_user:null;
			$pass = defined('qg_pass')?qg_pass:null;
			$Store = new Store(qg_host, $user, $pass);
		}
		return $Store;
	}

	// events
	static $events = [];
	static function on($name, $fn = null) {
		self::$events[$name][] = $fn;
	}
	static function fire($name, $data=null) {
		if (!isset(self::$events[$name])) return;
		$data['event_type'] = $name;
		foreach (self::$events[$name] as &$event) $event($data);
	}
	// token
	static function token() {
		return $_SESSION['qgToken'] ?? ($_SESSION['qgToken'] = randString(12));
	}
}
