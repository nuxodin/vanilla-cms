<?php
namespace qg;

class SettingsEditor {
	static $opened = null;
	function __construct($SET) {
		$this->SET = $SET;
		$_SESSION['SettingsEditor roots'][$SET->i] = 1;
	}
	function show() {
		html::addJSM(sysURL.'core/js/SettingsEditor.mjs');
		self::opened();
		return '<div class=qgSettingsEditor>'.$this->showItems($this->SET).'</div>';
	}
	static function showInput($S) {
		if ($S->count()) {
			return '<input type=hidden name="'.$S->i.'">'; // used?
		} else {
			$conf = [
				'type'  => $S->getHandler(),
				'name'  => $S->i,
				'value' => $S->getDefault()
			];
			$opts = $S->getOptions();
			if ($S->getHandler() === 'select') { // todo
				$conf['options'] = $opts;
			} else {
				if (isset($opts[0])) {
					foreach ($opts[0] as $opt => $v) {
						$conf[$opt] = $v;
					}
				}
			}
			return form_input($conf);
		}
	}
	static function showItems($SET) {
		//static $level = 0; zzz
		//$level++;
		//$s = '';

		$hasSub = false;
		foreach ($SET as $offset => $S) {
			if ($offset[0] === '_') continue;
			if ($S->count()) { $hasSub = true; break; }
		}

		$s = '<ul'.($hasSub?' class=-hasSub':'').'>';
		foreach ($SET as $offset => $S) {
			if ($offset[0] === '_') continue;
			$open = isset(self::$opened[$S->i]);
			$s .= '<li>';
			$s .= 	'<span class=-row>';
			$s .= 		'<span class=-toggle>';
			$s .= 			$S->count() ? '<a class="toggle -'.($open?'minus':'plus').'"></a>' : '<a class=toggle></a>';
			$s .=		'</span>';
			$s .= 		'<span class=-name>';
			$s .=			$offset;
			$s .=		'</span>';
			$s .=	 	'<span class=-inp>'.self::showInput($S).'</span>';
			$s .= 		'<span class=-rem><a>x</a></span>';
			$s .= 	'</span>';
			if ($open) $s .= self::showItems($S);
		}
		$s .= '</ul>';
		//$level--;
		return $s;
	}
	static function &opened() {
		if (self::$opened === null) {
			self::$opened = json_decode(G()->SET['qg']['settingsTree']['opened']->custom()->v, true);
		}
		return self::$opened;
	}
	static function open($id) {
		self::opened();
		self::$opened[$id] = 1;
		G()->SET['qg']['settingsTree']['opened'] = json_encode(self::$opened);
	}
	static function close($id) {
		self::opened();
		unset(self::$opened[$id]);
		G()->SET['qg']['settingsTree']['opened'] = json_encode(self::$opened);
	}
	static function access($id) {
		if (!isset($_SESSION['SettingsEditor roots'])) return false;
		$S = settingArray::getSetting((int)$id);
		if (!$S) return false;
		if (isset($_SESSION['SettingsEditor roots']['0'])) return $S;
		foreach ($_SESSION['SettingsEditor roots'] as $rootS => $egal) {
			$RootS = settingArray::getSetting($rootS);
			if (!$RootS) continue;
			if ($S->in($RootS)) return $S;
		}
		return false;
	}
}
