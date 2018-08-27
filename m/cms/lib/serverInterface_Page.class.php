<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class serverInterface_page {

	static $Cont = null;

	static function onBefore($fn, $pid) {
		if (!$pid) return false;
		self::$Cont = Page($pid);
		if (self::$Cont->access() < 1) {
			G()->Answer['cmsWarning'] = L('Ihnen fehlen die nötigen Berechtigungen den Inhalt zu sehen.');
			return false;
		}
		if (!self::$Cont->is()) {
			G()->Answer['cmsWarning'] = L('Die Seite existiert nicht.');
			return false;
		}
	}

	static function checkRight($access) {
		if (self::$Cont->access() < $access) {
			G()->Answer['cmsWarning'] = L('Ihnen fehlen die nötigen Berechtigungen.');
			return false;
		}
		return true;
	}

	static function api($pid, $vars=[]) {
		$Cont = Page($pid);
		return include $Cont->modPath.'page_api.php';
	}
	static function get($pid, $vars=[]) {
		$d['html'] = Page($pid)->get($vars);
		return $d;
	}
	static function getWithHead($pid, $vars=[]) {
		$d['html'] 	= Page($pid)->get($vars);
		G()->Answer['head'] = html::getHeader();
		return $d;
	}
	static function getPart($pid, $part, $vars=[]) {
		$str = Page($pid)->getPart($part, $vars);
		return $str;
	}
	static function reload($pid, $vars=[]) {
		$pid = (int)(string)$pid;
		$str = Page($pid)->get($vars);
		G()->Answer['replaceElements']['.-pid'.$pid] = $str;
	}
	static function loadPart($pid, $parts, $vars=[]) {
		$pid = (int)(string)$pid;
		foreach ((array)$parts as $part) {
			$str = Page($pid)->getPart($part,$vars);
			G()->Answer['updateElements']['.-pid'.$pid.' [data-part="'.$part.'"]'] = $str;
		}
	}

	static function setVisible($pid, $v) {
		if (!self::checkRight(2)) return false;
		Page($pid)->set('visible', $v);
		G()->Answer['cmsInfo'] = L('"Sichtbar in der Navigation" wurde geändert.');
	}
	static function setSearchable($pid, $v) {
		if (!self::checkRight(2)) return false;
		Page($pid)->set('searchable', $v);
		G()->Answer['cmsInfo'] = L('"Durchsuchbar" wurde geändert.');
	}
	static function setModule($pid, $v, $req=0) {
		if (!self::checkRight(2)) return false;
		$access = D()->one("SELECT access FROM module WHERE name = ".D()->quote($v)." ");
		if (!$access && !Usr()->superuser) return false;
		if ($req) {
			$done = 0; $has = 0;
			foreach (Page($pid)->Bough(['type'=>'p']) as $P) {
				++$has;
				if ($P->access() < 2) continue;
				$P->set('module', $v);
				++$done;
			}
			G()->Answer['cmsInfo'] = L('Bei ###1### von ###2### Seiten hatten Sie die Berechtigung das Modul zu ändern',$done,$has);
			return;
		}
		Page($pid)->set('module', $v);
		G()->Answer['cmsInfo'] = L('Das Layout wurde geändert.');
	}
	static function name($pid, $value) {
		if (!self::checkRight(2)) return false;
		Page($pid)->set('name', $value);
	}
	static function text($pid, $name, $l, $value=null) { // todo getter
		if (!self::checkRight(2)) return false;
		$changed = Page($pid)->Text($name, $l, $value);
		if ($changed !== false) G()->Answer['cmsInfo'] = L('Der Text wurde gespeichert.');
		return 1;
	}
	static function title($pid, $l, $value=null) { // todo getter
		if (!self::checkRight(2)) return false;
		$changed = Page($pid)->Title($l, $value);
		if ($changed !== false) G()->Answer['cmsInfo'] = L('Der Titel wurde gespeichert.');
		return 1;
	}
	static function onlineStart($pid, $v) {
		if (!self::checkRight(2)) return false;
		Page($pid)->set('online_start', $v);
		self::reload($pid);
      	G()->Answer['cmsInfo'] = L('Terminierung geändert');
	}
	static function onlineEnd($pid, $v) {
		if (!self::checkRight(2)) return false;
		Page($pid)->set('online_end', $v);
		self::reload($pid);
      	G()->Answer['cmsInfo'] = L('Die Terminierung wurde geändert.');
	}
	static function createChild($pid, $title) {
		if (!self::checkRight(2)) return false;
		if (!$title) return false;
		if ($id = Page($pid)->createChild()) {
			Page($id)->Title(L(), $title);
			Page($id)->changeUser(Usr(), 3);
			G()->Answer['cmsInfo'] = L('Die Seite wurde erstellt.');
			return Api::call('cms::toJson', [$id]);
		}
	}
	static function copy($pid, $deep=false) {
		if (!self::checkRight(2)) return false;
		$Page = Page($pid)->copy($deep, function($P){
			if ($P->access() < 1) return false; // $access() < 2?
		});
		$Page->changeUser(Usr(), 3);
		$title = $Page->Title();
		$title = trim($title) ? $title.' (copy)' : '';
		$Page->Title(L(), $title);
		G()->Answer['cmsInfo'] = L('Die Seite wurde kopiert.');
		return (string)$Page->id;
	}
	// access
	static function setPublic($pid, $v) {
		if (!self::checkRight(3)) return false;
		Page($pid)->set('access', $v);
		Page($pid)->changeUser(Usr(),3);
		G()->Answer['cmsInfo'] = L('Die Berechtigungen wurden geändert.');
		return (bool)$v;
	}
	static function changeUser($pid, $usr_id, $access, $req=false) {
		if (!self::checkRight(3)) return false;
		Page($pid)->changeUser($usr_id, $access);
		if ($req) {
			$done = 0; $has = 0;
			foreach (Page($pid)->Bough(['type'=>'p']) as $P) {
				++$has;
				if ($P->access() < 3) continue;
				$P->changeUser($usr_id, $access);
				++$done;
			}
			G()->Answer['cmsInfo'] = L('Bei ###1### von ###1### Seiten hatten Sie die Berechtigung das Zugriffsrecht zu ändern',$done,$has);
			return;
		}
		G()->Answer['cmsInfo'] = L('Die Benutzer-Berechtigungen wurden geändert.');
		return true;
	}
	static function changeGroup($pid, $grp_id, $access) {
		if (!self::checkRight(3)) return false;
		Page($pid)->changeGroup($grp_id, $access);
		G()->Answer['cmsInfo'] = L('Die Gruppen-Berechtigungen wurden geändert.');
		return true;
	}
	static function remove($pid) {
		if (!self::checkRight(2)) return false;
		$P = Page($pid);
		$return['parent_id'] = (int)(string)$P->Parent();
		$trash = G()->SET['cms']['pageTrash']->v;
		if ($P->in($trash)) {
			if (!self::checkRight(3)) return false;
			$P->Parent()->removeChild($P);
		} else {
			$P->SET['__deleted_from']   = (string)$P->Parent();
			$P->SET['__deleted_before'] = (string)$P->nextSibling(['type'=>$P->vs['type']]);
			$P->SET['__deleted_time']   = time();
			$TrashPage = Page($trash);
			$TrashPage->insertBefore($P, $TrashPage->Cont('main'));
			//$P->set('access', 0); handled by ->Bough() and there it checks null!
			//$P->onlineEnd(time()); // no effect!? // why this
			foreach ($P->Bough() as $Child) {
				$Child->vs['access'] !== null && $Child->set('access', 0);
				D()->query("DELETE FROM page_access_usr WHERE page_id = '".$Child."' AND access < 2 ");
				D()->query("DELETE FROM page_access_grp WHERE page_id = '".$Child."' AND access < 2 ");
			}
		}
		G()->Answer['cmsInfo'] = L(($P->vs['type']==='c'?'Der Inhalt':'Die Seite').' wurde gelöscht.');
		return $return;
	}

	static function insertBefore($parent, $page, $before=null) {
		if (!self::checkRight(2)) return false;
		$Parent = Page($parent);
		$Page = Page($page);
		if ($Page->access() < 2) { G()->Answer['cmsWarning'] = L('Sie besitzen auf der Zielseite nicht die benötigten Berechtigungen!'); return; }
		if ($Parent->in($Page))  { G()->Answer['cmsError'] = L('Die gewünschte Basis befindet sich innerhalb dieser Seite, dies würde eine Endlosschleife produzieren!'); return; }
		if ($Parent->insertBefore($Page,$before)) { G()->Answer['cmsInfo'] = L('Der Inhalt wurde verschoben.'); }
	}

	static function addContent($pid, $mod) { // todo: module access check?
		if (!self::checkRight(2)) return false;
		$Cont = Page($pid)->createCont(['module' => $mod]);
		$Cont->changeUser(Usr(), 3);
		$d['html'] 	= $Cont->get();
		$d['id'] 	= $Cont->id;
		G()->Answer = [
			'cmsInfo' => L('Der neue Inhalt wurde erstellt.'),
			'head' => html::getHeader()
		];
		return $d;
	}
	// settings
	static function setDefault($pid, $v, $reload=true) {
		if (!self::checkRight(2)) return false;
		Page($pid)->SET->setDefault($v);
		if (Page($pid)->vs['type'] === 'c' && $reload) {
			return self::reload($pid);
		} else {
			G()->Answer['cmsInfo'] = L('Die Einstellung wurde geändert.');
		}
	}
	static function setDefaultById($pid, $id, $v) {
		if (!self::checkRight(2)) return false;
		$S = settingArray::getSetting($id);
		if (!$S->in( Page($pid)->SET )) { G()->Answer['cmsWarning'] = L('Ihnen fehlen die nötigen Berechtigungen.'); return; }
		$S->setDefault($v);
		return self::reload($pid);
	}
	static function setRemoveById($pid, $id) {
		if (!self::checkRight(2)) return false;
		$S = settingArray::getSetting($id);
		if (!$S->in( Page($pid)->SET )) { G()->Answer['cmsWarning'] = L('Ihnen fehlen die nötigen Berechtigungen.'); return; }
		$P = $S->Parent();
		unset($P[$S->offset()]);
		return self::reload($pid);
	}
	static function setRemove($pid, $n) {
		if (!self::checkRight(2)) return false;
		if (Page($pid)->SET->has($n)) {
			$S = Page($pid)->SET[$n];
			$P = $S->Parent();
			unset($P[$S->offset()]);
		}
		return true;
	}
	static function setUser($pid, $v) {
		Page($pid)->SET->setUser($v);
		return 1;
	}
	static function setUserById($pid, $id, $v) {
		$S = settingArray::getSetting($id);
		if (Page($pid)->access() < 1 || !$S->in(Page($pid)->SET)) { G()->Answer['cmsWarning'] = L('Ihnen fehlen die nötigen Berechtigungen.'); return; }
		$S->setUser($v);
		return 1;
	}
	/* Files */
	static function FileDelete($pid, $name) {
		if (!self::checkRight(2)) return false;
		$done = Page($pid)->FileDelete($name);
		G()->Answer['cmsInfo'] = L('Die Datei wurde gelöscht.');
		self::reload($pid);
		return $done;
	}
	static function FilesSort($pid, $sort) {
		if (!self::checkRight(2)) return false;
		Page($pid)->FilesSort($sort);
		G()->Answer['cmsInfo'] = L('Die Dateien wurden sortiert.');
		return self::reload($pid);
	}
	static function FileAdd($pid, $file=null, $replace=null) {
		if (!self::checkRight(2)) return false;
		// file access check
		if (is_numeric($file)) {
			$file = dbFile($file);
			if (!$file->access()) {
				trigger_error('No access ('.$file.')');
				G()->Answer['cmsInfo'] = L('Ihnen fehlen die nötigen Berechtigungen.');
				return;
			}
			if ($replace) {
				$File = Page($pid)->File($replace);
				$file->clone($File);
			} else {
				$file = $file->clone();
				$File = Page($pid)->FileAdd($file);
			}
		} else {
			if ($file !== null && !preg_match('/^https?:\/\//', $file)) {
				trigger_error('other then http/https not allowed ('.$file.')');
				G()->Answer['cmsInfo'] = L('Ihnen fehlen die nötigen Berechtigungen.');
				return;
			} else {
				$File = Page($pid)->FileAdd($file, $replace); // todo, "replace" should nod simply add a file with the same "name"
			}
		}

		G()->Answer['cmsInfo'] = L('Die Datei wurde hinzugefügt.');
		return ['url'=>$File->url(), 'name'=>$File->name()];
	}
	static function filesSetOrder($pid, $what) {
		if (!self::checkRight(2)) return false;
		$P = Page($pid);
		$i = 1;
		switch ($what) {
			case 'date':
				$sql =
				" SELECT pf.* 						" .
				" FROM file f, page_file pf			" . // todo draft mode table('file'), table('page_file')
				" WHERE 							" .
				"	f.id = pf.file_id 				" .
				"	AND pf.page_id = ".$P." 		" .
				" ORDER BY f.log_id 				";
				break;
			case 'reverse':
				$sql =
				" SELECT pf.* 						" .
				" FROM file f, page_file pf			" .
				" WHERE 							" .
				"	f.id = pf.file_id 				" .
				"	AND pf.page_id = ".$P." 		" .
				" ORDER BY pf.sort DESC				";
				break;
			case 'name':
			case 'name_reverse':
				$sql =
				" SELECT pf.name, f.name AS fname	" .
				" FROM file f, page_file pf			" .
				" WHERE 							" .
				"	f.id = pf.file_id 				" .
				"	AND pf.page_id = ".$P." 		" .
				" ORDER BY f.name					";
				$vs = D()->indexCol($sql);
				if ($what === 'name_reverse') {
					foreach ($vs as $n => $v) $vs[$n] = strrev($v);
				}
				natsort($vs);
				foreach ($vs as $name => $egal) {
					D()->page_file->update([
						'page_id' => $P,
						'name' => $name,
						'sort' => $i++
					]);
				}
				self::reload($pid);
				return 1;
				break;
		}
		foreach (D()->query($sql) as $vs) {
			D()->page_file->update([
				'page_id' => $P,
				'name' => $vs['name'],
				'sort' => $i++
			]);
		}
		self::reload($pid);
		return 1;
	}
	static function filesDeleteDouble($pid) {
		if (!self::checkRight(2)) return false;
		$P = Page($pid);
		foreach ($P->Files() as $name => $F) {
			if ($F->vs['md5'] && isset( $fs[$F->vs['md5']] )) {
				self::FileDelete($pid, $name);
			}
			$fs[$F->vs['md5']] = 1;
		}
		self::reload($pid);
		return 1;
	}
	static function filesDeleteAll($pid) {
		if (!self::checkRight(2)) return false;
		$P = Page($pid);
		foreach ($P->Files() as $name => $F) {
			self::FileDelete($pid, $name);
		}
		self::reload($pid);
		return 1;
	}
	// urls
	static function urlCustomSet($pid , $lang , $url) {
		if (!self::checkRight(2)) return false;
		Page($pid)->urlSet($lang, [
			'url' => $url,
			'custom' => 1
		]);
		return Page($pid)->urlSeoGen($lang); // todo: move to page::urlSet() ?
	}
	static function urlCustomUnset($pid , $lang) {
		if (!self::checkRight(2)) return false;
		Page($pid)->urlSet($lang, [
			'custom' => 0
		]);
		return Page($pid)->urlSeoGen($lang); // todo: move to page::urlSet() ?
	}
	static function urlTargetSet($pid, $lang, $v) {
		if (!self::checkRight(2)) return false;
		Page($pid)->urlSet($lang, [
			'target' => $v
		]);
		return 1;
	}
	// redirects
	static function requestAdd($pid, $v) { // todo rename to redirectAdd
		if (!self::checkRight(2)) return false;
		if (serverInterface_Cms::requestUsed($v)) { G()->Answer['cmsWarning'] = L('Diese URL wird bereits verwendet.'); return; }
		D()->query("INSERT INTO page_redirect SET request = ".D()->quote($v).", redirect=".D()->quote($pid)." ");
		return 1;
	}
	static function requestDel($pid, $v) {
		if (!self::checkRight(2)) return false;
		D()->query("DELETE FROM page_redirect WHERE request = ".D()->quote($v)." AND redirect=".D()->quote($pid)." ");
		return 1;
	}
	static function addClass($pid, $class, $options = null) {
		if (!self::checkRight(2)) return false;
		Page($pid)->addClass($class, $options);
	}
	static function removeClass($pid, $class) {
		if (!self::checkRight(2)) return false;
		Page($pid)->removeClass($class);
	}
}
