<?php
namespace qg;

// update cross-space-fields if in space.
// should we also delete if in space?
qg::on('dbTable::update-before', function($e){
	if (!vers::$space) return;
	extract($e, EXTR_REFS);   // Table, id, data
	if ($Table->_name !== 'page') return;
	$liveData = [];
	foreach ($data as $key => $value) {
		if ($key === 'sort' || $key === 'basis' || $key === 'access' || $key === 'title_id') { // title_id ??? remove it!
			$liveData[$key] = $value;
		}
	}
	if (!$liveData) return;
	$row = D()->row("SELECT * FROM page WHERE ".$Table->entryId2where($id));
	if ($row['type'] !== 'p') {
		unset($liveData['sort']);
		unset($liveData['basis']);
	};
	if (!$liveData) return;
	D()->query("UPDATE page       SET ".$Table->valuesToSet($liveData)." WHERE ".$Table->entryId2where($id));
	D()->query("UPDATE _vers_page SET ".$Table->valuesToSet($liveData)." WHERE ".$Table->entryId2where($id));
});

// settings, add event listener before vers.events.php
$handleSettingsManipulations = function($e){
	if (!cms_vers::$space) return;
	if (vers::$tableEntriesCopying) return; // todo: check if needed...
	if ($e['Table']->_name !== 'qg_setting') return;

	if ($e['event_type'] === 'dbTable::insert-before' && G()->SET['cms']['pages']->i == $e['data']['basis']) {
		vers::setSpace(cms_vers::$space);
		return;
	}

	$Page = cms_vers::SettingToPage($e['id'] ?? $e['data']['basis']); // basis for insert
	if (!$Page) return;
	if ($Page->access() < 2) return;
	vers::setSpace(cms_vers::$space);
};
qg::on('dbTable::insert-before', $handleSettingsManipulations);
qg::on('dbTable::update-before', $handleSettingsManipulations);
qg::on('dbTable::delete-before', $handleSettingsManipulations);

require_once 'vers.events.php';
require_once 'vers.class.php';
require_once 'cms_vers.class.php';

///////////////////////////////////////////////////////////////////////////////
// which table/fields should be versionable?
// indexed fields should come from the version-table (performance)
vers::$db['page'] = [
	'id'         => 1,
	'log_id'     => 1,
	'log_id_ch'  => 1,
	'type'       => 1,
	'basis'      => 1,
	'sort'       => 1,
	'module'     => 1,
	//'access'     => 1, new: access from main-table
	'visible'    => 1,
	'searchable' => 1,
	'title_id'   => 1,
	'name'       => 1,
	'_cache'     => 1,
];
vers::$db['page_file']  = true;
vers::$db['page_class'] = true;
vers::$db['page_text']  = true;
vers::$db['page_url']   = true;
vers::$db['text']       = true;
vers::$db['file']       = true;
vers::$db['qg_setting'] = true;


if (G()->SET['cms.versions']['draftmode']->setType('bool')->v) {
	cms_vers::$space = G()->SET['cms']['editmode']->v ? 1 : 0;
} else {
	cms_vers::$space = 0;
}
if (isset($_GET['qgCmsVersSpace']) && $_GET['qgCmsVersSpace'] !== 'active') {
	cms_vers::$space = (int)$_GET['qgCmsVersSpace'];
}
cms_vers::$log = (int)($_GET['qgCmsVersLog'] ?? 0);

// space but not log
if (cms_vers::$space && !cms_vers::$log) {

	/////////////////////////////////////////////////////////////////////////
	// read data
	/////////////////////////////////////////////////////////////////////////

	qg::on('page::construct',function($data){

		if (!liveLog::$id) trigger_error('page construct before action! '.$data['Page']->id);

		extract($data, EXTR_REFS); // $Page
		if (!$Page->vs) { // if not exists search in space
			$Page->vs = D()->row("SELECT *, ".cms_vers::$space." AS vers_space FROM ".vers::view('page',cms_vers::$space)." WHERE id = ".$Page->id);
		}
		if (!$Page->vs) { // if not exists search live
			$Page->vs = D()->row("SELECT * FROM page WHERE id = ".$Page->id);
		}
		if (!$Page->vs) return;
		$space       = $Page->vs['vers_space'] ?? 0;
		$spaceNeeded = $Page->access() < 2 ? 0 : cms_vers::$space;
		if ($space != $spaceNeeded) {
			$Page->vs = D()->row($Page->sql("SELECT * FROM page WHERE id = ".$Page->id));
		}

		if (!$Page->vs) return;
		if ($spaceNeeded != 0) {
			$oldSpace = vers::setSpace($spaceNeeded);
			!$Page->vs['title_id'] && $Page->set('title_id', TextPro::generate()->id);
			//$Page->SET->get();
			cms_vers::page_load_runtime_cache($Page); // on new created page-object, title_id is 0!
			//$x = $Page->SET;
			//$Page->__destruct(); // save cache!
			vers::setSpace($oldSpace);
		}
	});
	qg::on('page::children',function($data){
		$Page = $data['Page'];
		if ($Page->Children !== null) return;
		$rows1 = D()->all("SELECT *, ".cms_vers::$space." AS vers_space FROM ".vers::view('page',cms_vers::$space)." WHERE basis = ".$Page->id." ORDER BY type DESC, sort");
		$rows2 = D()->all("SELECT * FROM page WHERE basis = ".$Page->id." ORDER BY type DESC, sort");
		$rows = array_merge($rows1, $rows2);
		$Page->Children = [];
		foreach ($rows AS $row) {
			$id = $row['id'];
			if (isset($Page->Children[$id])) continue;
			$Child = cms::Page($id, $row);
			if (!$Child->is()) continue;                    // values can be changed in the constructor
			if ($Child->vs['basis'] != $Page->id) continue; // values can be changed in the constructor
			$Page->Children[$id] = $Child;
			if (isset($row['name'])) $Page->Named[$row['type']][$row['name']] = $Child;
		}
	});
	qg::on('page::sql',function($data){
		extract($data, EXTR_REFS); // $Page, $sql
		if ($Page->access() < 2) return $sql;
		$sql = trim($sql);
		if (substr($sql,0,7) !== 'SELECT ') return;
		$sql = preg_replace_callback('/SELECT( .*? )FROM( .*?)($| WHERE | LIMIT | ORDER )(.*)/', function($matches){
			list(,$select,$from,$keyword,$rest) = $matches;
			$from = preg_replace_callback('/(^|,| LEFT JOIN | INNER JOIN ).*?([a-z0-9_]+)( [a-z_0-9]*|)/',function($m){
				list(,$join,$table,$alias) = $m;
				$table = trim($table);
				$alias = trim($alias);
				if (!$alias) $alias = $table;
				if (isset(vers::$db[$table])) $table = vers::view($table,cms_vers::$space,0);
				return ' '.trim($join).' '.trim($table).' '.$alias.' ';
			},$from);
			return 'SELECT'.$select.', '.cms_vers::$space.' AS vers_space FROM '.trim($from).' '.$keyword.$rest;
		}, $sql);
	});

	$root_id = G()->SET['cms']['pages']->i;
	qg::on('setting::construct', function($e) use($root_id) { // $id, &$vs
		$vs = $e['vs']; // temporary values
		if (!$vs) { // does not exist in space
			$table = vers::$space ? 'qg_setting' : '_vers_qg_setting'; // search in other table
			$vs = D()->row("SELECT * FROM ".$table." WHERE id = ".$e['id']);
			if (!$vs) return;
		}
		if (!$vs['basis']) return; // at the end
		$Page = null; // find the page
		$nextOffset = $vs['offset'];
		$nextBasis  = $vs['basis'];
		while ((int)$nextBasis) {
			if ($nextBasis == $root_id) {
				$Page = Page($nextOffset);
				break;
			}
			$Tmp = settingArray::getSetting($nextBasis);
			// if (isset($e['Setting']->CmsPage)) {
			// 	$Page = $e['Setting']->CmsPage;
			// 	break;
			// }
			$nextOffset = $Tmp->k;
			$nextBasis  = $Tmp->b;
		}
		$e['Setting']->CmsPage = $Page; // runtime cache Page
		$spaceNeeded = $Page && $Page->access() > 1 ? cms_vers::$space : 0;
		if ($spaceNeeded == vers::$space) return;
		// get values from right space
		$e['vs'] = D()->row("SELECT * FROM ".vers::view('qg_setting',$spaceNeeded)." WHERE id = ".$e['id']);
	});
	$getSettingChildren = function($e){
		if (!isset($e['Setting']->CmsPage)) return; // settings constructed before eventlistener added
		$Page = $e['Setting']->CmsPage;
		if ($Page && $Page->access() > 1) {
			vers::setSpace(cms_vers::$space);
		}
	};
	qg::on('setting::getAll-before',    $getSettingChildren);
	qg::on('setting::getOffset-before', $getSettingChildren);
	qg::on('setting::getAll-after',     function($e){ vers::setSpace(0); });
	qg::on('setting::getOffset-after',  function($e){ vers::setSpace(0); });

	////////////////////////////////////////////////////////////////////////
	// write data
	/////////////////////////////////////////////////////////////////////////

	/* api::before needed needed especially for serverInterface_cms::setTxt() */
	qg::on('Api::before', function($e){ // $fn, $args
		if (substr($e['fn'],0,6) === 'page::') {
			$Page = Page($e['args'][0]);
			if ($Page->access() < 2) return;
			vers::setSpace(cms_vers::$space);
		}
		if (substr($e['fn'],0,5) === 'cms::') { // on every method?
			vers::setSpace(cms_vers::$space);
		}
	});
	qg::on('Api::after', function(){ vers::setSpace(0); });
	/**/

	$onModifyBefore = function() {
		if (vers::$tableEntriesCopying) return;
		vers::setSpace(cms_vers::$space);
	};
	$onModifyAfter = function($e) {
		if (vers::$tableEntriesCopying) return;
		// text into runtime cache before return to space 0
		isset($e['original_event']) && $e['original_event'] === 'page::text_set-after' && $e['Page']->Text($e['name'],$e['lang']);
		vers::setSpace(0);
	};
	qg::on('page::modify-before',      $onModifyBefore);
	qg::on('page::modify-after',       $onModifyAfter);
	qg::on('page::file_upload-before', $onModifyBefore);
	qg::on('page::file_upload-after',  $onModifyAfter);
}

// after above eventlisteners added
require_once 'history.php';

qg::on('action', function(){

	vers::ensureSpace(cms_vers::$space); // ensure space (todo: better solution?)

	if (!cms_vers::$log) return;

	$space = $_GET['qgCmsVersSpace'] ?? cms_vers::$space;
	cms_vers::$space = $space = $space === 'active' ? cms_vers::$space : (int)$space;

	$pid = $_GET['qgCmsVersPage'];
	// if (Page($pid)->access() < 2) return; // needed?
	cms_vers::preventDbManipulations();
	cms_vers::$log && cms_vers::cacheHeaders();
	G()->SET['cms']['editmode']->setUser(0); qg::on('background',function(){ G()->SET['cms']['editmode']->setUser(1); }); // disable editmode

	$generate = null;
	$generate = function ($pid) use(&$generate) {
		$Cont = Page($pid);
		foreach ($Cont->Conts() as $SubCont) $generate($SubCont->id); // todo, select only conts and no pages
		if ($Cont->access() < 2) return;
		$Cont->vs['online_start'] = $Cont->vs['online_end'] = 0; // always online
		if (G()->SET['cms']['pages']->has($Cont->id)) {
			$Cont->SET->get(); // not needed in space only?
		} else {
			//$Cont->_SET = []; // bad
		}
		cms_vers::page_load_runtime_cache($Cont);
	};

	G()->SET['cms']['pages']; // otherwise, index pages not found..., settingArray::$All sould clear all items...?
	G()->SET['cms']['pageNotFound']->v;
	$oldVers = vers::setVers(cms_vers::$space, cms_vers::$log);
	settingArray::$All = cms::$_Pages = dbFile::$All = [];
	$generate($pid);
	vers::setVers($oldVers);
});

//////////////////////////////////////////////////////////////
// divers, not main-logic
//////////////////////////////////////////////////////////////

qg::on('cms-ready', function(){
	if (!Page()->edit) return;
	if (isset($_GET['qgCmsNoFrontend'])) return;
	html::addJsFile(sysURL.'cms.versions/pub/vers.js');
	html::addJsFile(sysURL.'cms.versions/pub/comparer.js');
	if (G()->SET['cms.versions']['draftmode']->v) {
		$versions = D()->indexCol('SELECT space, unix_timestamp(changed_page) FROM vers_cms_page_changed WHERE page_id = '.Page().'');
		if (isset($versions[1]) && (!isset($versions[0]) || $versions[1] > $versions[0])) { // no live or draft younger then live
			G()->js_data['cms_vers_draft_changed'] = true;
		}
		html::addJsFile(sysURL.'cms.versions/pub/draftmode.js');
	}
});

qg::on('dbFile-remove-fs', function($e){
	extract($e, EXTR_REFS);   // $dbFile, $prevent
	if (D()->one("SELECT id FROM _vers_file WHERE md5 = ".D()->quote($dbFile->vs['md5']))) {
		$prevent = true;
	}
});

// file-access
qg::on('file_ouput-before',function(){
	// todo, test if file in page with access
	if (isset($_SESSION['cms_vers::space']) && $_SESSION['cms_vers::space']) {
		vers::setSpace($_SESSION['cms_vers::space']);
	}
	if (isset($_SESSION['cms_vers::log']) && $_SESSION['cms_vers::log']) {
		vers::setLog($_SESSION['cms_vers::log']);
	}
	//vers::setSpace(cms_vers::$space);
});
qg::on('deliverHtml',function(){
	$_SESSION['cms_vers::log']   = cms_vers::$log;
	$_SESSION['cms_vers::space'] = cms_vers::$space;
});
