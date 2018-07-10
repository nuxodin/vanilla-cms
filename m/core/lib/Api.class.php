<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class Api {
    static $tokenCheckNeeded = true;
	static function loadLibs() {
		static $loaded = false;
		if ($loaded) return;
		foreach (qg::$modules AS $name => $m) {
			$file = sysPATH.$name.'/serverInterface.php';
			is_file($file) && require_once($file);
		}
		$loaded = true;
	}
	static function call($fn, $args) { // todo: $args = [] default param?
		self::loadLibs();
		$ret = null;
		$onAfter = false;
		$ok = true;

		// before
		if (preg_match('/(.+)::(.+)/', $fn, $matches)) {
			$class = 'qg\serverInterface_'.$matches[1];
			$method = $matches[2];
			$onBefore = $class.'::onBefore';
			$onAfter = $class.'::onAfter';
			if (is_callable($onBefore)) {
				$aspectArgs = $args;
				array_unshift($aspectArgs, $method);
				$v = call_user_func_array($onBefore, $aspectArgs);
				$ok = $v !== false;
			}
		}

        if (self::$tokenCheckNeeded) {
            self::checkToken();
        }
        self::$tokenCheckNeeded = true; // ensure next api access checks token!

		if ($ok) {
			qg::fire('Api::before', ['fn'=>$fn,'args'=>&$args]);
			$ret = call_user_func_array('qg\serverInterface_'.$fn, $args);
			qg::fire('Api::after', ['fn'=>$fn,'args'=>&$args,'return'=>&$ret]);
		}

		// after
		if ($onAfter) {
			if (is_callable($onAfter)) {
				$aspectArgs = $args;
				array_unshift($aspectArgs, $method);
				call_user_func_array($onAfter, $aspectArgs);
			}
		}
		return $ret;
	}
    static function checkToken() {
        if (!isset(G()->ASK['serverInterface'])) return; // no request from the client, no need to test token
        if (!isset($_POST['qgToken'])) {
            trigger_error('hacking? qgToken not set');
            exit;
        }
        if ($_POST['qgToken'] !== qg::token()) {
            Answer([
                'cmsError' => 'Die Session ist nicht gültig, Bitte neu laden!',
                'info'     => 'qgToken nicht gültig'
            ]);
        }
    }
}
