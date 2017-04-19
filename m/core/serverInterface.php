<?php
namespace qg;

class serverInterface_core {
	static function changePw($old, $new) {
		if (!Usr()->is()) return 0;
		if (!auth::pw_verify($old, Usr()->pw)) return -1;
		if (strlen($new) < 5) return -2;
		Usr()->pw = auth::pw_hash($new);
		return 1;
	}
	static function login($email, $pw) {
		$error = auth::auth($email, $pw);
		return [
			'success' => $error,
			'token'  => 'todo',
		];
	}
}
class serverInterface_SettingsEditor {
	static $SET = false;
	static function set($id, $value) {
		self::$SET->setDefault($value);
		return true;
	}
	static function open($id) {
		SettingsEditor::open($id);
		return SettingsEditor::showItems(self::$SET);
	}
	static function close($id) {
		SettingsEditor::close($id);
		return true;
	}
	static function remove($id) {
		$P = self::$SET->Parent();
		if (!$P) return;
		unset($P[self::$SET->k]);
		return true;
	}
	static function onBefore($fn, $id) {
		self::$SET = SettingsEditor::access($id);
		if (!self::$SET) {
			trigger_error('settings_editor no accesss!');
			return false;
		}
	}
}
