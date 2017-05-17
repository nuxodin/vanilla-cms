<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class serverInterface_cms{
	static function searchPagesByTitle($search, $filter=[]) {
		$search = str_replace('cmspid://','',$search);
		$sql =
		" SELECT 												 " .
		"	p.id AS id 											 " .
		" FROM 													 " .
		"	page p,												 " .
		"	text t												 " .
		" WHERE 1												 " .
		"	AND ( p.type = 'p' OR p.visible ) 					 " .
		"	AND t.lang = '".L()."' 			                     " .
		"	AND p.title_id = t.id 								 " .
		"	AND ( p.id = ".D()->quote($search)."                 " .
		"        OR t.text LIKE ".D()->quote('%'.$search.'%')." ) " .
		" ORDER BY 												 " .
		"	p.id   = ".D()->quote($search)."	DESC,			 " .
		"	t.text = ".D()->quote($search)."	DESC,    		 " .
		"	t.text LIKE ".D()->quote($search.'%')."	     DESC,	 " .
		"	t.text LIKE ".D()->quote('% '.$search.'%')." DESC,	 " .
		"	t.text ASC											 " .
		" LIMIT 20												 " .
		"";
		$res = [];
		foreach (D()->query($sql) as $vs) {
			$Page = Page($vs['id']);
			if (!$Page->access()) continue;
			if (!trim($Page->title())) continue;
			$Obj = [
				'html' =>
					'<b>'.$Page->title().'</b> ('.($Page->vs['type']==='c'?'Content':'Page').' '.$Page.')' .
						($Page->Parent()?
						'<i style="font-size:10px;display:block">'.$Page->Parent()->Title().'</i>'.
							($Page->Parent()->Parent()?
							'<i style="font-size:10px;display:block">'.$Page->Parent()->Parent()->Title().'</i>'
							:'')
						:''),
				'text'  => trim($Page->Title())?strip_tags(trim($Page->Title())).' ('.$Page.')' : $Page->id,
				'value' => $Page->id
			];
			$res[] = $Obj;
		}
		return $res;
	}
	static function searchFile($s) {
		$sql =
		" SELECT 											" .
		"	pf.page_id AS pid, f.* 							" .
		" FROM 												" .
		"	page_file pf									" .
		"	,file f											" .
		" WHERE 1											" .
		"	AND pf.file_id = f.id							" .
		"	AND ( 											" .
		"		   f.id = ".D()->quote($s)." 				" .
		"		OR f.name LIKE ".D()->quote('%'.$s.'%')."	" .
		"		OR f.text LIKE ".D()->quote($s.'%')."	    " .
		//"		OR f.text LIKE ".D()->quote('%'.$s.'%')."	" .
		"	)  												" .
		" GROUP BY f.id 									" .
		" ORDER BY 											" .
		"	f.id    =   ".D()->quote($s)."			DESC,	" .
		"	f.name  =   ".D()->quote($s)."			DESC,	" .
		"	f.name LIKE ".D()->quote($s.'%')."		DESC,	" .
		"	f.name LIKE ".D()->quote('% '.$s.'%')."	DESC,	" .
		"	f.text  =   ".D()->quote($s)."			DESC,	" .
		"	f.text LIKE ".D()->quote($s.'%')."		DESC,	" .
		//"	f.text LIKE ".D()->quote('% '.$s.'%')."	DESC,	" .
		"	f.name ASC										" .
		"";
		$res = [];
		$i = 0;
		$used = [];
		foreach (D()->query($sql) as $vs) {
			$Page = Page($vs['pid']);
			if ($Page->access() < 2) continue;
			$File = dbFile($vs['id'],$vs);
			if (!$File->exists()) continue;
			if ($i++ > 10) break;
			if ($used[$vs['md5']]??false) continue;
			$used[$vs['md5']] = true;
			switch ($File->extension()) {
				case 'jpg' :
				case 'jpeg' :
				case 'gif' :
				case 'svg' :
				case 'png' :
					$imgSrc = $File->url().'/w-32/h-32/img.jpg';
					break;
				default:
					//$imgSrc = sysURL.'cms/pub/util/fileicons/32/default.icon.gif';
					$imgSrc = 'about:blank';
			}
			$Obj = [
				'html'  => '<div style="background:url('.$imgSrc.') no-repeat center; width:32px; height:32px; float:left; display:block; margin-right:3px"></div><b>'.$vs['name'].'</b><br><i>' .$Page->Page->title().'</i>',
				'text'  => $vs['name'],
				'value' => $File->id,
			];
			$res[] = $Obj;
		}
		return $res;
	}
	static function requestUsed($v) {
		$r = D()->one("SELECT request FROM page_redirect WHERE request = ".D()->quote($v)." ");
		$u = D()->one("SELECT url FROM ".table('page_url')." WHERE url = ".D()->quote($v)." ");
		return $r || $u;
	}
	static function setTxt($id, $v) {
		$vs = D()->row("SELECT name, page_id FROM ".table('page_text')." WHERE text_id = ".(int)$id." ");
		if ($vs) return Api::call('page::text', [$vs['page_id'], $vs['name'], L(), $v]);
		$vs = D()->row("SELECT id FROM ".table('page')." WHERE title_id = ".(int)$id." ");
		if ($vs) return Api::call('page::title', [$vs['id'], L(), $v]);
		return false;
	}
	static function pidFromTxtId($id) {
		$pid = D()->one("SELECT page_id FROM ".table('page_text')." WHERE text_id = ".(int)$id." ");
		if ($pid) return $pid;
		return D()->one("SELECT id FROM ".table('page')." WHERE title_id = ".(int)$id." ");
	}
	static function toJson($Page, $type='*') {
		$Page = Page($Page);
		$title = trim($Page->Title()) ? (string)$Page->title() : '-';
		$node = [
			'title'    => $title,
			'title_id' => $Page->Title()->id,
			'isLazy'   => count($Page->Children(['type'=>$type])),
			'key'      => (int)(string)$Page,
			'url'      => $Page->url(),
			'myaccess' => $Page->access(),
			'visible'  => (int)$Page->vs['visible'],
			'online'   => (int)$Page->isOnline(),
			'public'   => (int)$Page->isPublic(),
			'type'     => $Page->vs['type'],
			'module'   => $Page->vs['module'],
			'name'     => (string)$Page->vs['name'],
		];
		return $node;
	}
	static function getTree($Start, $opt=[]) {
		if (!isset($opt['filter'])) $opt['filter'] = '*';
		static $level = 1;
		$res = [];
		$Cs = ((string)$Start == 0) ? [Page(1)] : Page($Start)->Children(['type'=>$opt['filter']]);
		foreach ($Cs as $C) {
			$node = self::toJson($C, $opt['filter']);
			if (( !isset($opt['in']) || Page($opt['in'])->in($C) ) && (!isset($opt['level']) || $opt['level'] > $level)) {
				$level++;
				$node['children'] = self::getTree($C,$opt);
				$node['state']['open'] = true;
				$level--;
			}
			$res[] = $node;
		}
		return $res;
	}
	static function clipboardSet($pid) {
		if ($pid && Page($pid)->access() < 2) return;
		G()->SET['cms']['clipboard']->setUser((int)$pid);
		if ($pid) G()->Answer = ['cmsInfo'=>L('Fügen Sie den ausgeschnittenen Inhalt auf einer anderen Seite wieder ein.')];
	}
}
