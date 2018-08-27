<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

// // Anwendung:
// function functionToCall($name) {
//     return 'Hello '.$name;
// }
// $Hash = hashAction::add('\qg\functionToCall', 60*60, 1)->arguments('World');
// echo 'expires at: '.strftime('%x %X', $Hash->expires);
// // 5 years later:
// try {
//     echo hashAction::fire($Hash); // 'Hallo World'
// } catch (\Exception $e) {}

class dbEntry_qg_hashaction extends dbEntry {
	function arguments() {
		$this->data = serialize(func_get_args());
		$this->save();
		return $this;
	}
}
class hashAction {
	static function add($fn, $expires_in=60*60*24, $times=2147483644, $hashSize=32) {
		for ($i=10; $i; $i--) {
			$hash = randString((int)$hashSize);
			if (!D()->one("SELECT hash FROM qg_hashaction WHERE hash = '".$hash."'")) break;
		}
		D()->qg_hashaction->insert([
			'hash'    => $hash,
			'log_id'  => liveLog::$id,
			'fn'      => $fn,
			'expires' => $expires_in ? time() + $expires_in : 0,
			'times'   => $times,
		]);
		return D()->qg_hashaction->Entry($hash);
	}
	static function fire($hash) {
		$Entry = D()->qg_hashaction->Entry($hash);
		if (!$Entry->is())                                throw new \Exception('hashAction: hash does not (or not anymore) exist');
		if ($Entry->expires && $Entry->expires < time())  throw new \Exception('hash is expired');
		if ($Entry->times < 1)                            throw new \Exception('hashAction: limit reached');
		--$Entry->times;
		return call_user_func_array($Entry->fn, unserialize($Entry->data));
	}
	static function clean() {
		D()->query("DELETE FROM qg_hashaction WHERE times <= 0 OR (expires AND expires < '".time()."')");
	}
}
