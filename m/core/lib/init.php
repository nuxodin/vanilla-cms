<?php
namespace qg;

class liveClient {
	static $id;
	static function init() {
		!isset($_COOKIE['cid']) && self::register(); // not registred
		if (!isset($_SESSION['liveClient'])) {
			$_SESSION['liveClient'] = D()->one("SELECT id FROM client WHERE hash = ".D()->quote($_COOKIE['cid']));
			!$_SESSION['liveClient'] && self::register(); // session cookie is set but not in db!
		}
		self::$id = $_SESSION['liveClient'];
	}
	static function register() {
		$hash = base64_encode(random_bytes(24));
		setcookie('cid', $hash, 2004929530, appURL, false, QG_HTTPS, true);
		$_COOKIE['cid'] = $hash;
		$_SESSION['liveClient'] = D()->client->insert([
			'hash'    => $hash,
			'browser' => $_SERVER['HTTP_USER_AGENT']
		]);
	}
}
class liveSess {
	static $maxpause = 0;
	static $id = null;
	static function init() {
		!isset($_SESSION) && session_start();
		if (self::$maxpause && isset($_SESSION['qgLastAccessTime']) && $_SESSION['qgLastAccessTime'] + self::$maxpause < time()) {
			$_SESSION = [];
		}
		$_SESSION['qgLastAccessTime'] = time();
		liveClient::init();
		Auth::listen();
		if (!isset($_SESSION['liveSess'])) {
			$_SESSION['liveSess'] = D()->sess->insert([
				'ip'        => $_SERVER['REMOTE_ADDR'],
				'usr_id'    => Usr(),
				'client_id' => liveClient::$id,
				'time'      => time()
			]);
		}
		self::$id = $_SESSION['liveSess'];
	}
}
class liveLog {
	static $id;
	static function init() {
		self::$id = D()->log->insert([
			'time'    => time(),
			'sess_id' => liveSess::$id,
			'url'     => URL($_SERVER['REQUEST_URI']),
			'post'    => count($_POST) ? serialize($_POST) : '',
			'referer' => $_SERVER['HTTP_REFERER']
		]);
	}
}
