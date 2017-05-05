<?php
namespace qg;

class qg {

	// init
	static public $modules = [];
	static function need($mName) {
		if (isset(self::$modules[$mName])) return;
		$path = sysPATH.$mName.'/';
		!file_exists($path) && self::Store()->download($mName);
		is_file($path.'qg.php') && require_once $path.'qg.php';
		!self::isInstalled($mName) && self::install($mName); // todo v5: install before require qg.php?
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

	// install
	static function install($module) {
		time_limit(600);
		$_x_module = $module;
		$path = sysPATH.$module.'/';
		is_file($path.'/dbscheme.xml') && dbScheme::check(file_get_contents($path.'dbscheme.xml'));
		is_file($path.'install.php')   && require_once($path.'install.php');
		self::setInstalled($_x_module);
	}

	static function Store(){
		static $Store = null;
		if (!$Store) {
			$user = defined('qg_user') ? qg_user : null;
			$pass = defined('qg_pass') ? qg_pass : null;
			$Store = new Store(qg_host, $user, $pass);
		}
		return $Store;
	}

	/* installed */
	static $installedData = null;
	static function isInstalled(&$m) {
		self::$installedData === null && self::getInstalledData();
		return isset(self::$installedData[$m]);
	}
	static function setInstalled($m, $do = true) {
		$data = &self::getInstalledData();
		if ($do) $data[$m] = 1;
		else unset($data[$m]);
		$file = appPATH.'qg/qgInstalled.txt';
		!is_dir(appPATH.'qg') && mkdir(appPATH.'qg');
		file_put_contents($file, serialize($data));
	}
	static function &getInstalledData() {
		if (self::$installedData === null) {
			$file = appPATH.'qg/qgInstalled.txt';
			if (is_file($file))
				self::$installedData = unserialize(file_get_contents($file));
		}
		return self::$installedData;
	}
	static function token(){
		return $_SESSION['qgToken'] ?? ($_SESSION['qgToken'] = randString(12));
	}
}
