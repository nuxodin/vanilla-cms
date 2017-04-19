<?php
namespace qg;

class serverInterface_cms_vers {
	//static function publishCont($pid, $fromLog, $toSpace=null){
	static function publishCont($pid, $options){
		$Cont = Page($pid);
		if ($Cont->access() < 2) return false;
		$options += ['fromSpace'=>cms_vers::$space, 'fromLog'=>0, 'toSpace'=>cms_vers::$space, 'subPages'=>false];
		cms_vers::publishCont($pid, $options['fromSpace'], $options['fromLog'], $options['toSpace'], $options['subPages']);
	}
	static function getForPage($pid) {
		$data = vers_protocol_for_page_and_conts($pid);
		// combine same versions
		$nData = [];
		foreach($data as $row) $nData[$row['vers']] = $row;
		// sort
		ksort($nData);
		// numeric array
		$data = array_values($nData);
		return $data;
	}
	static function logDetails($id) {
		$id   = (int)$id;
		$row  = D()->row("SELECT * FROM log WHERE id = ".$id);
		$post = unserialize($row['post']);
		$ask  = $post['askJSON'];
		$data = json_decode($ask,1);
		$translateFn = [ // first parameter has to be page
			'page::addContent'        => 'Inhalt hinzugefügt',
			'page::insertBefore'      => 'Position geändert',
			'page::setDefault'        => 'Einstellung geändert',
			'page::setDefaultById'    => 'Einstellung geändert',
			'page::FilesSort'         => 'Dateireihenfolge geändet',
			'page::filesSetOrder'     => 'Dateien sortiert',
			'page::addDbFile'         => 'Datei hinzugefügt',
			'page::FileDelete'        => 'Datei gelöscht',
			'page::filesDeleteDouble' => 'Doppelte Dateien gelöscht',
			'page::copy'              => 'Kopiert',
			'page::remove'            => 'Gelöscht',
			'page::setModule'         => 'Modul geändert',
			'page::addClass'          => 'Tag hinzugefügt',
			'page::removeClass'       => 'Tag entfernt',
			'page::setVisible'        => 'Sichtbarkeit geändert',
			'cms_vers::rollBackCont'  => 'Stand zurückgesetzt',
		];
		$ignoreFn = [
			'cms_frontend_1::widget' => 1,
			'cms::getTree' => 1,
			'Page::get' => 1,
			'Page::getWithHead' => 1,
			'page::reload' => 1,
			'page::setPublic'  => 1,
			'page::onlineStart'  => 1,
			'page::onlineEnd' => 1,
		];
		$ContOrPage = function($Page) {
			$title = trim(strip_tags($Page->Title()));
			$title = $title ? '"'.$title.'"' : '';
			return '<div mark=".-pid'.$Page.'">'.($Page->vs['type']==='p'?'Seite':'Inhalt').' '.$title.' ('.$Page->id.') </div>';
		};
		$messages = [];
		if (isset($data['serverInterface'])) foreach ($data['serverInterface'] as $call) {
			$fn = $call['fn'];
			if (isset($ignoreFn[$fn])) continue;
			$args = $call['args'];
			switch ($fn) {
				case 'cms::setTxt':
					if ($vs = D()->row("SELECT name, page_id FROM ".vers::view('page_text',vers::$space,0)." WHERE text_id = ".(int)$args[0]." ")) {
						$Page = Page($vs['page_id']);
						$messages[] = $ContOrPage($Page).'  Text "'.hee($vs['name']).'" geändert';
					} else if ($vs = D()->row("SELECT id FROM page WHERE title_id = ".(int)$args[0]." ")) {
						$Page = Page($vs['id']);
						$messages[] = $ContOrPage($Page).' Titel geändert';
					}
				break;
				case 'page::insertBefore':
					$Page = Page($args[1]);
					$messages[] = $ContOrPage($Page).' '.$translateFn[$fn];
				break;
				case 'SettingsEditor::set':
				case 'Setting':
					// todo: get the corresponding page
					$messages[] = 'Einstellung geändert';
				break;
				default:
					if (isset($translateFn[$fn])) {
						$Page = Page($args[0]);
						$messages[] = $ContOrPage($Page).' '.$translateFn[$fn];
					} else {
						$messages[] = '<span style="color:red">'.$fn.'</span>';
					}
			}
		}
		$Log    = D()->log->Entry($row);
		$Sess   = Sess($Log->sess_id);
		$Usr    = $Sess->Usr();
		$Client = D()->client->Entry($Sess->client_id);
		return [
			'messages'   => $messages,
			'usr'        => $Usr->is() ? $Usr->email : 'guest',
			'url'        => $Log->url,
			'referer'    => $Log->referer,
			'ip'         => $Sess->ip,
			'browser'    => ua_to_browser($Client->browser),
			'time'       => (int)$Log->time,
			'user_agent' => $Client->browser,
		];
	}
}

// problem: beinhaltet nur Inhalte die momentan zur Seite gehören.
function vers_protocol_for_page_and_conts($pid) {
	$data = vers_protocol_for_page($pid);
	foreach (Page($pid)->Conts() as $C) {
//		echo $C->vs['module'];
		$data = array_merge($data, vers_protocol_for_page_and_conts($C->id));
	}
	return $data;
}

function vers_protocol_for_page($pid) {
	$protocol = array_merge(
		vers_protocol('page',       't.id = '.$pid),
		vers_protocol('page_class', 't.page_id = '.$pid),
		//vers_protocol('page_file',  't.page_id = '.$pid), // irrelevant
		//vers_protocol('page_text',  't.page_id = '.$pid), // irrelevant
		//vers_protocol('page_url',   't.page_id = '.$pid),
		vers_protocol('text',       't.id = (SELECT title_id FROM '.vers::view('page',     cms_vers::$space,0).' WHERE id = '.$pid.')'),
		vers_protocol('text',       't.id IN(SELECT text_id  FROM '.vers::view('page_text',cms_vers::$space,0).' WHERE page_id = '.$pid.')'), // what about deleted texts?
		vers_protocol('file',       't.id IN(SELECT file_id  FROM '.vers::view('page_file',cms_vers::$space,0).' WHERE page_id = '.$pid.')')  // what about deleted files?
	);
	if (G()->SET['cms']['pages']->has($pid)) {
		$protocol = array_merge($protocol, vers_protocol_for_settings(G()->SET['cms']['pages'][$pid]));
	}
	return $protocol;
}
function vers_protocol_for_settings($SET) {
	$protocol = vers_protocol('qg_setting', 't.id = '.$SET->i);
	foreach ($SET as $S) {
		$protocol = array_merge($protocol, vers_protocol_for_settings($S));
	}
	return $protocol;
}
function vers_protocol($table, $where = 1) {
	$sql =
	"
	SELECT
		t._vers_log, l.time, l.sess_id
	FROM
		_vers_".$table." t
		LEFT JOIN log l ON t._vers_log = l.id
	WHERE
		t._vers_space = '".cms_vers::$space."' AND
		t._vers_log > 0 AND
		$where
	ORDER BY t._vers_log
	";
	$data = [];
	foreach (D()->all($sql) as $row) {
		$Usr = Sess($row['sess_id'])->Usr();
		$email = $Usr->is() ? $Usr->email : 'guest';
		$data[] = [
			'vers' => $row['_vers_log'],
			'time' => (int)$row['time'],
			'usr'  => $email,
		];
	}
	return $data;
}


function ua_to_browser($str) {
	$str = preg_replace('/Trident.+rv:([0-9.]+)/', 'IE/$1', $str);
	$str = preg_replace('/Version\/([0-9.]+).+Safari\/([0-9.]+).+/', 'Safari/$1', $str);
	$str = preg_replace('/MSIE ([0-9.]+)/', 'IE/$1', $str);
	$str = preg_replace('/Edge\//', 'IE/', $str);
	$str = preg_replace('/(Mozilla|AppleWebKit|Trident|Gecko)\//', '', $str);
	preg_match('/([a-zA-Z]+)\/([0-9]+\.[0-9])/', $str, $matches);
	if ($matches) {
		list($x, $vendor, $version) = $matches;
		return $vendor.' '.$version;
	} else {
		return 'other';
	}
}
