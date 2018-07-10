<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class Auth {
	static function listen() {
		if (isset($_POST['liveUser_login'])) {
			$saveLogin = isset($_POST['save_login']) ? (int)(bool)$_POST['save_login'] : 0;
			$error = (int)self::auth($_POST['email'], $_POST['pw'] ?? '');
			$translateError = [
				'0'  => 'password',
				'-1' => 'username',
				'-2' => 'inactive',
				'1'  => '',
			];
			G()->loginError = $translateError[$error];
			self::rememberLogin($saveLogin);
		}
		if (isset($_POST['liveUser_logout'])) {
			self::logout();
		}
		if (isset($_GET['liveUser_logout'])) {
			self::logout();
			header('Location: '.Url()->stripParam('liveUser_logout'));
			exit;
		}
		if (!isset($_SESSION['liveUser'])) {
			if ($uid = Client()->usr_id) self::auth(Usr($uid)->email);
		}
	}
	static function auth($email, $pw='') {
		qg::fire('auth-before',['email'=>&$email, 'pw'=>&$pw]);
		$sql =  " SELECT * FROM usr 									         " .
				" WHERE LOWER(TRIM(email)) = LOWER(".D()->quote(trim($email)).") " ;
		$user = D()->row($sql);
		if (!$user)           return -1;
		if (!$user['active']) return -2;
		$Usr = Usr($user['id']);
		$rehash = self::pw_needs_rehash($Usr->pw);
		if (!$rehash) {
			$ClientUsrs = Client()->Usrs();
			if (isset($ClientUsrs[$Usr->id]) && $ClientUsrs[$Usr->id]->save_login) return self::login($Usr->id);
		}
		if (!self::pw_verify($pw, $Usr->pw)) return 0;
		if ($rehash) $Usr->pw = self::pw_hash($pw);
		return self::login($Usr->id);
	}
	static function pw_hash($pw) {
		return password_hash($pw, PASSWORD_DEFAULT);
	}
	static function pw_verify($pw, $hash) {
		return password_verify($pw, $hash) || md5($pw) === $hash; // md5 zzz
	}
	static function pw_needs_rehash($hash) {
		return !preg_match('/^\$/', $hash) || password_needs_rehash($hash, PASSWORD_DEFAULT); // preg_match zzz
	}
	static function login($id) {
		$id = (int)(string)$id;
		if (!D()->one("SELECT id FROM usr WHERE id = ".$id)) return false;
		$old_session = $_SESSION;
		self::logout();
		$_SESSION['liveUser'] = $id;
		Client()->addUsr($id);
		Client()->usr_id = $id;
		qg::fire('login', ['session_old'=>$old_session]);
		return true;
	}
	static function logout() {
		self::rememberLogin(0);
		Client()->usr_id = 0;
		$_SESSION = [];
		//session_destroy();
	}
	static function rememberLogin($do) {
		if (!Usr()->is()) return;
		$E = D()->client_usr->Entry([
			'usr_id' => Usr(),
			'client_id' => Client()
		]);
		$E->save_login = (int)$do;
	}
}
