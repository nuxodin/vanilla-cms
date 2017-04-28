<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class Page {

	public $id			= 0;
	public $vs			= [];
	public $Texts       = null;
	public $_Files      = null;
	public $_FilesAll   = null;

	protected $Parent     = null;
	protected $Path       = null;
	public    $Children     = null;
	public    $Named      = [];
	protected $Title 	  = null;
	protected $Conts      = null;
	protected $is         = true;
	public    $_SET       = null;

	public $cacheChanged = false;

	function __construct($id = 0, $vs = 0) {
		$this->id  = (int)$id;
		cms::$_Pages[$this->id] = $this;
		$this->vs = $vs ?: D()->row("SELECT * FROM ".table('page')." WHERE id = ".$this->id);
		qg::fire('page::construct', ['Page'=>$this]);
		if (!$this->vs) {
			$this->id_not_exists = $this->id;
			$this->id = G()->SET['cms']['pageNotFound']->v;
			$this->vs = cms::Page($this->id)->vs;
			$this->is = false;
			return;
		}
		!$this->vs['title_id'] && $this->set('title_id', TextPro::generate()->id);
	}

	function sql($sql) {
		qg::fire('page::sql', ['Page'=>$this, 'sql'=>&$sql]);
		return $sql;
	}

	function cache($index, $value = null) {
		$this->cache; // get cache, on first access its a overloaded property...
		$this->cache[$index] = $value;
		$this->cacheChanged = true;
	}
	function __destruct() {
		if (!$this->cacheChanged) return;
		foreach ($this->cache as $key => $values) if (count($values) > 200) unset($this->cache[$key]);
		$this->set('_cache', serialize($this->cache));
	}

	function is() {
		return $this->is;
	}
	function __get($n) {
		switch ($n) {
			case 'SET':
				if ($this->_SET === null) {
					$this->_SET = G()->SET['cms']['pages'][$this->id];
					if (!$this->_SET) trigger_error('page SET not created');
					$this->_SET->getAll();
				}
				return $this->_SET;
			case 'modPath':
				return $this->modPath = sysPATH.$this->vs['module'].'/';
			case 'modUrl':
				return $this->modUrl  = sysURL.$this->vs['module'].'/';
			case 'Page':
				return $this->Page    = $this->vs['type']==='p' || !$this->Parent() ? $this : $this->Parent()->Page;
			case 'edit':
				return $this->edit    = $this->access() > 1 && G()->SET['cms']['editmode']->v;
			case 'cache':
				return $this->cache   = unserialize($this->vs['_cache']);
			default:
				$GLOBALS['skip_stacks'] += 1;
				trigger_error('page::__get('.$n.') not implemented');
				$GLOBALS['skip_stacks'] -= 1;
		}
	}
	function set($data, $value=null) {
		if (!$this->is) {
			$GLOBALS['skip_stacks'] += 1;
			trigger_error('Page '.$this->id_not_exists.' does not exist! (::set)');
			$GLOBALS['skip_stacks'] -= 1;
			return;
		}
		if (is_string($data)) $data = [$data => $value];
		if ($this->vs === false) trigger_error('vs false?');
		$this->vs = $data + $this->vs;
		D()->page->update($this->id, $data);
	}

	// access
	private $usrAccess = [];
	function access($Usr=null) {
		//if (!$this->is) return 0;
		if ($Usr === null) $Usr = Usr();
		if (!isset($this->usrAccess[$Usr->id])) {
			$this->usrAccess[$Usr->id] = $this->_usrAccess($Usr);
		}
		return $this->usrAccess[$Usr->id];
	}
	private function _usrAccess($Usr) {
		if ($Usr->superuser) return 3;
		if ($this->vs['access'] === null && $this->Parent()) {
			$parentAccess = $this->Parent->access($Usr);
			return $Usr->is() ? max($parentAccess, $this->usrAccessOnly($Usr)) : $parentAccess;
		}
		if (!$Usr->is()) return (int)(bool)$this->vs['access'];
		$access = max($this->vs['access'], $this->usrAccessOnly($Usr));
		if ($access === 3) return 3;
		$sql =
		" SELECT max(access) AS access				     ".
		" FROM 	page_access_grp 					     ".
		" WHERE 									     ".
		"		grp_id != 0 AND                          ".
		"		page_id = ".$this." AND				     ".
		"		grp_id IN(".implode(',',$Usr->grps()).") ";
		$grpAccess = D()->one($sql);
		return max($access, $grpAccess);
	}
	private function usrAccessOnly($Usr) {
		$sql =
		" SELECT access 				" .
		" FROM page_access_usr 			" .
		" WHERE                         " .
		"   page_id = ".$this->id." AND " .
		"	usr_id = ".$Usr->id."       " ;
		return D()->one($sql);
	}

	/* render */
	function get($vars=[]) { // get for output / rename to read output or print?
		if (!$this->isReadable()) return '';
		if (isset(cms::$RenderPath[$this->id])) {
			return $this->edit ? '<div class="qgCmsCont -pid'.$this.'">Recursion, Content '.$this->id.' again!</div>' : '';
		}
		cms::$RenderPath[$this->id] = true;
		$s = $this->getPrepared($vars);
		$modPath = sysPATH.$this->vs['module'].'/';
		is_file($modPath.'pub/main.js')  && html::addJsFile($this->modUrl.'pub/main.js');
		is_file($modPath.'pub/main.css') && html::addCSSFile($this->modUrl.'pub/main.css');
		array_pop(cms::$RenderPath);
		return $s;
	}
	function getPrepared($vars=[]) {

		qg::fire('page::parseTemplate-before', ['Cont'=>$this, 'string'=>&$str]);
		if ($str !== null) return $str;

		$str = trim($this->getRaw($vars));
		if (!$str) $str = '<div></div>';
		$class = 'qgCms'.($this->vs['type']==='c'?'Cont':'Page').' -pid'.$this->id.' -m-'.str_replace('.','-',$this->vs['module']);
		$expose = cms::classesExposeCss();
		if ($expose)
			foreach ($this->classes() as $name => $egal)
				if (isset($expose[$name]))
					$class .= ' '.$name;

		if ($this->edit) $class .= ' -e';
		$id = '';
		if ($this->vs['type']==='c' && $this->vs['visible']) {
			$id = ' id="'.hee(substr($this->urlSeo(L()), 1)).'"';
		}
		$done = null;
		$ret = preg_replace('/^<([^>]+)class=("([^"]*)"|([^\s>]*))/','<$1class="$3$4 '.$class.'"'.$id, $str, 1, $done);
		if ($done) return $ret;
		$ret = preg_replace('/^<([^\s>]+)([\s]?)/','<$1 class="'.$class.'"'.$id.'$2', $str, 1, $done);
		if ($done) return $ret;
		return '<div class="'.$class.'"'.$id.'>'.$ret.'</div>';
	}
	function getRaw($vars=[]) {
	   	if (!$this->is) { trigger_error('Seite existiert nicht!'); return; }
	   	$modPath = sysPATH.$this->vs['module'].'/';
	   	$Cont = $this;
		$res = is_file($modPath.'control.php') ? include $modPath.'control.php' : null;
		if (!is_file($modPath.'index.php')) return '<div>'.($this->edit ? L('Das Modul existiert nicht!') : '').'</div>';
		$T = new template((array)$res + ['vars'=>$vars,'Cont'=>$this]);
		qg::fire('page::render-before', ['Page'=>$this]);
		$str = $T->get($modPath.'index.php');
		qg::fire('page::render-after', ['Page'=>$this, 'string'=>&$str]);
        return $str;
	}
	function getPart($part, $vars=[]) {
		if (preg_match('"[\\/]"', $part)) return false;
		$modPath = sysPATH.$this->vs['module'].'/';
	   	$Cont = $this;
		$res = is_file($modPath.'control.php') ? include $modPath.'control.php' : null;
		$T = new Template((array)$res + ['vars'=>$vars, 'Cont'=>$this]); //$T->assign('vars', $vars); //$T->assign('Cont', $this);
		return $T->get($modPath.'parts/'.$part.'.php');
	}

	function onlineStart() {
		return $this->vs['online_start'] === null && $this->Parent() ? $this->Parent()->onlineStart() : (int)$this->vs['online_start'];
	}
	function onlineEnd() {
		return $this->vs['online_end']   === null && $this->Parent() ? $this->Parent()->onlineEnd()   : (int)$this->vs['online_end'];
	}
	function isOnline() {
		$start = $this->onlineStart();
		$end   = $this->onlineEnd();
		$now   = time() + 32;
		return ( $start === 0 || $now > $start ) && ( $end === 0 || $now < $end );
	}
	function isReadable() {
		return $this->edit || ( $this->access() > 0 && $this->isOnline() );
	}
	function isPublic() {
		return $this->vs['access'] === null && $this->Parent() ? $this->Parent()->isPublic() : $this->vs['access'];
	}
	function accessInheritParent() {
		return $this->vs['access'] === null && $this->Parent() ? $this->Parent()->accessInheritParent() : $this;
	}

	/* traversing */
	function Children($filter=false) {
		qg::fire('page::children',['Page'=>$this]);
		if ($this->Children === null) {
			$sql = "SELECT * FROM ".table('page')." WHERE basis = ".$this->id." ORDER BY type DESC, sort, id DESC";
			$this->Children = [];
			foreach (D()->query($sql) AS $row) {
				$id = $row['id'];
				$Child = cms::Page($id, $row);
				$this->Children[$id] = $Child;
				if (isset($row['name'])) $this->Named[$row['type']][$row['name']] = $Child;
			}
		}
		return cms::filter($this->Children, $filter);
	}
	function Conts() {
		if ($this->Conts === null) {
			$this->Conts = [];
			foreach ($this->Children(['type'=>'c']) as $C) $this->Conts[] = $C;
		}
		return $this->Conts;
	}
	function Parent($level=null) {
		if ($this->Parent === null) {
			$this->Parent = $this->vs['basis'] ? cms::Page($this->vs['basis']) : false;
		}
		if ($level === null) return $this->Parent;
		$level = (int)$level;
		$i = 0;
		foreach ($this->Path() as $P) if ($i++ === $level) return $P;
		return false;
	}
	function &Path() {
		if ($this->Path === null) {
			$Parent = $this->Parent();
			$this->Path = $Parent ? $Parent->Path() : [];
			$this->Path[$this->id] = $this;
		}
		return $this->Path;
	}
	function in($Page) {
		$Path = &$this->Path();
		return isset($Path[(string)$Page]);
	}
	function &Bough($filter = false) {
		$Bough[$this->id] = $this;
		foreach ($this->Children(['type'=>'*']) as $Child) {
			$Bough += $Child->Bough();
		}
		if ($filter) $Bough = cms::filter($Bough, $filter);
		return $Bough;
	}

	/* texts */
	function Texts() {
		if (!isset($this->cache['texts'])) {
			$this->cache('texts', D()->indexCol($this->sql("SELECT name, text_id FROM ".table('page_text')." WHERE page_id = ".$this)));
		}
		if ($this->Texts === null) {
			$this->Texts = [];
			foreach ($this->cache['texts'] AS $name => $id) {
				$this->Texts[$name] = TextPro($id);
				$this->Texts[$name]->edit = $this->edit;
			}
		}
		return $this->Texts;
	}
	function Text($name='main', $lang=null, $value=null) {
		$this->Texts();
		if (!isset($this->Texts[$name])) {
			qg::fire('page::text_generate-before', ['Page'=>$this, 'name'=>$name]);
			$T = TextPro::generate();
			qg::fire('page::text_generate-after',  ['Page'=>$this, 'name'=>$name]);
			D()->page_text->insert(['name'=>$name, 'page_id'=>$this, 'text_id'=>$T->id]);
			$this->Texts[$name] = $T;
			$this->Texts[$name]->edit = $this->edit;
			$this->cache('texts');
		}
		if ($lang === null)  return $this->Texts[$name];
		$TextLang = $this->Texts[$name]->get($lang);
		if ($value === null) return $TextLang->get();

		if ($TextLang->get() === $value) return false; // no chang

		qg::fire('page::text_set-before', ['Page'=>$this, 'name'=>$name, 'lang'=>&$lang, 'value'=>&$value]);
		$TextLang->set($value);
		qg::fire('page::text_set-after',  ['Page'=>$this, 'name'=>$name, 'lang'=>&$lang, 'value'=>&$value]);
	}
	function TextDelete($name) {
		$this->Texts();
		if (isset($this->Texts[$name])) {
			$T = $this->Texts[$name];
			D()->text->delete($T->id);
			D()->page_text->delete(['page_id'=>$this,'text_id'=>$T->id]);
			unset($this->Texts[$name]);
			$this->cache('texts');
		}
	}
	function Title($lang = null, $value = null) {
		if ($this->Title === null) {
			$this->Title = TextPro($this->vs['title_id']);
			$this->Title->edit = $this->edit;
		}
		if ($lang  === null) return $this->Title;
		if ($value === null) return $this->Title->get($lang)->get();
		qg::fire('page::title_set-before', ['Page'=>$this, 'lang'=>&$lang, 'value'=>&$value]);
		$this->Title->get($lang)->set($value);
		qg::fire('page::title_set-after',  ['Page'=>$this, 'lang'=>&$lang, 'value'=>&$value]);
		$this->urlsSeoGen();
	}

	/* manipulate tree */
	function createChild($vs=[]) {
		$vs += [
			'log_id'	   => liveLog::$id,
			'basis'		   => $this->id,
			'online_start' => time(),
			'access'       => $this->vs['access'],
			'module'	   => $this->vs['module'],
			'searchable'   => $this->vs['searchable'],
			'type'		   => 'p',
			'visible'      => 1,
		];
		$id = D()->page->insert($vs);
		$Page = cms::Page($id);
		if (!$id) return $Page;

		foreach (D()->all("SELECT * FROM page_access_usr WHERE page_id = ".$this->id) as $data) {
			D()->page_access_usr->insert(['page_id'=>$Page] + $data);
		}
		foreach (D()->all("SELECT * FROM page_access_grp WHERE page_id = ".$this->id) as $data) {
			D()->page_access_grp->insert(['page_id'=>$Page] + $data);
		}
		if ($vs['type'] === 'p') {
			if (G()->SET['cms']['pages']->has($this->id) && $this->SET->has('childXML') && $this->SET['childXML']) {
				$Page->fromXML($this->SET['childXML']);
			}
		}
		foreach ($this->Children(['type'=>$vs['type']]) as $C) {
			$C->set('sort', $C->vs['sort']+1);
		}
		// pre generate (cache) so it does not trigger a "undo step" (cms.versions) later
		// $Page->urlsSeoGen(); // needs title
		$Page->Texts();
		$Page->Files();
		$Page->Classes();
		// clear runtime-cache
		$this->Children = $this->Conts = $this->Named = null;
		return $Page;
	}
	/* Contents */
	function createCont($vs=[]) {
		$vs += [
			'type'         => 'c',
			'module'       => 'cms.cont.flexible',
			'visible'      => '',
			'online_start' => null,
			'access'       => null
		];
		$Cont = $this->createChild($vs);
		return $Cont;
	}
	function removeChild($Child) {
		$P = cms::Page($Child);
		$Children = $this->Children(['type'=>'*']);
		if (!isset($Children[(string)$P])) return;
		foreach ($P->Children(['type'=>'*']) as $C) $P->removeChild($C);
		foreach ($P->Files() as $name => $F) $P->FileDelete($name);
		foreach ($P->Texts() as $name => $T) $P->TextDelete($name);
		D()->page->delete($P);
		// clear runtime-cache
		$this->Children = $this->Conts = $this->Named = null;
		qg::fire('cms::manipulate_tree');
		return true;
	}
	function copy($deep=false, $if=false) {
		if ($if && $if($this) === false) return false;
		$id = D()->page->copy('id', $this->id);
		$Page = cms::Page($id);
		$Page->set([
			'log_id'   => liveLog::$id,
			'title_id' => $this->title()->copy()->id,
			'_cache'   => ''
		]);
		$Page->SET->setDefault($this->SET); // todo: only set if has
		foreach ($this->Texts() as $name => $Text) {
			D()->page_text->insert(['page_id'=>$Page->id, 'text_id'=>$Text->copy()->id, 'name'=>$name]);
		}
		foreach ($this->Files() as $name => $File) {
			D()->page_file->insert(['page_id'=>$Page->id, 'file_id'=>$File->Clone(), 'name'=>$name]);
		}
		foreach ($this->Children(['type'=> $deep?'*':'c']) as $Cont) {
			$Copy = $Cont->copy($deep, $if);
			if ($Copy) {
				$Copy->set('basis',$Page);
				$Copy->Parent = null; // bad: old Parent cached!!
			}
		}
		$Page->cache = []; // used?
		qg::fire('cms::manipulate_tree');
		return $Page;
	}
	function insertBefore($Page, $Before=null) {
		$Page      = cms::Page($Page);
		$OldParent = $Page->Parent();
		if ($Before) $Before = cms::Page($Before);
		if ($this->in($Page)) return false;
		$type = $Page->vs['type'];

		$sort = null;
		$i = 1;
		foreach ($this->Children(['type'=>$type]) as $Child) {
			if ($Page   === $Child) continue;
			if ($Before === $Child) $sort = $i++;
			$Child->set('sort', $i++);
		}
		$sort = $sort !== null ? $sort : $i++;
		$Page->set(['basis'=>$this, 'sort'=>$sort]);
		// clear cache
		$Page->Parent = $Page->Path = null;
		$this->Children = $this->Named = null;
		$OldParent->Children = $OldParent->Named = null;
		$Page->urlsSeoGen();

		qg::fire('cms::manipulate_tree');
		return true;
	}

	/* set access */
	function changeUser($Usr, $access) {
		$vs = ['page_id'=>$this, 'usr_id'=>$Usr, 'access'=>$access];
		$access == 0 ? D()->page_access_usr->delete($vs) : D()->page_access_usr->ensure($vs);
		return $this;
	}
	function changeGroup($Grp, $access) {
		$vs = ['page_id'=>$this, 'grp_id'=>$Grp, 'access'=>$access];
		$access == 0 ? D()->page_access_grp->delete($vs) : D()->page_access_grp->ensure($vs);
		return $this;
	}

	/* url */
	function urls() {
		if (!isset($this->cache['urls'])) {
			$urls = [];
			foreach (D()->all($this->sql("SELECT lang, url, target FROM ".table('page_url')." WHERE page_id = ".$this)) as $row) {
				$urls[$row['lang']] = $row;
			}
			$this->cache('urls',$urls);
		}
		return $this->cache['urls'];
	}
	function url($lang = null) {
		if ($lang === null) $lang = L();
		$hash = $this->vs['type'] === 'c' ? $this->urlSeo($lang) : '';
		return $this->edit ? appURL.'?cmspid='.$this->Page.'&changeLanguage='.$lang.$hash : appURL.$this->Page->urlSeo($lang).$hash;
	}
	function urlSeo($lang) {
		$urls = $this->urls();
		if (!isset($urls[$lang])) {
			$urls[$lang] = ['url'=>$this->urlSeoGen($lang), 'target'=>''];
		}
		return $urls[$lang]['url'];
	}
	function urlSet($lang, $data) {
		$data = [
			'page_id' => $this->id,
			'lang' => $lang,
		] + $data;
		// better?
		// $urls = $this->urls();
		// isset($urls[$lang]) ? D()->page_url->update($data) : D()->page_url->insert($data);
		$row = D()->row($this->sql("SELECT * FROM page_url WHERE page_id = ".$this." AND lang = ".D()->quote($lang)));
		$row ? D()->page_url->update($data) : D()->page_url->insert($data);
		// D()->page_url->ensure([
		// 	'page_id' => $this->id,
		// 	'lang' => $lang,
		// ] + $data);
		$this->cache('urls'); // remove cache
	}
	function urlSeoGenerated($lang){
		if ($this->vs['type'] === 'c') {
			return '#cmspid'.$this;
		}
		$base = !$this->Parent() || $this->Parent()->id == 1 ? $lang : $this->Parent()->urlSeo($lang);
		$part = urlize($this->Title()->getTranslated($lang));
		$url = ($base === '' || substr($base, -1) === '/') ? $base.$part : $base.'/'.$part;
		//$sql = "SELECT page_id FROM ".table('page_url')." WHERE url = ".D()->quote($url)." AND !(page_id = ".$this." AND lang = '".$lang."')";
		$sql = $this->sql("SELECT page_id FROM page_url WHERE url = ".D()->quote($url)." AND !(page_id = ".$this." AND lang = '".$lang."')");
		if (file_exists(appPATH.$url) || D()->one($sql)) {
			$url .= '-'.$lang.$this;
		}
		return $url;
	}
	function urlSeoGen($lang) {
		//$sql = "SELECT * FROM ".table('page_url')." WHERE page_id = ".$this." AND lang = ".D()->quote($lang);
		$sql = $this->sql("SELECT * FROM page_url WHERE page_id = ".$this." AND lang = ".D()->quote($lang));
		$row = D()->row($sql);
		$url = $row && $row['custom'] ? $row['url'] : $this->urlSeoGenerated($lang);
		$this->urlSet($lang, ['url'=>$url]);
		foreach ($this->Children(['type'=>'*']) as $C) {
			$C->urlSeoGen($lang);
		}
		return $url;
	}
	function urlsSeoGen() {
		foreach (L::all() as $l) $this->urlSeoGen($l);
	}

	/* files */
	function Files() {
		if (!isset($this->_Files)) {
			$this->_Files = $this->_FilesAll = [];
			$sql = " SELECT f.*, pf.name as pf_name                        ".
				   " FROM                                                  ".
				   "    ".table('page_file')." pf                          ".
				   "    LEFT JOIN ".table('file')." f ON f.id = pf.file_id ".
				   " WHERE pf.page_id = ".$this."                          ".
				   " ORDER BY sort	                                       ";
			foreach (D()->query($this->sql($sql)) AS $vs) {
				$F = $this->_FilesAll[$vs['pf_name']] = dbFile($vs['id'], $vs);
				if ($F->exists()) $this->_Files[$vs['pf_name']] = $F;
			}
		}
		return $this->_Files;
	}
	function File($name) {
		$this->Files();
		if (!isset($this->_FilesAll[$name])) return $this->FileAdd(null, $name);
		return $this->_FilesAll[$name];
	}
	function FileAdd($file=null, $name=null) {
		$File = ($file instanceof dbFile) ? $file : dbFile::add($file);
		if ($name === null) $name = '_'.randString(7);
		D()->page_file->insert([
			'page_id' => $this,
			'file_id' => $File,
			'name'    => $name,
			'sort'    => D()->one($this->sql("SELECT min(sort) FROM page_file WHERE page_id = ".$this))-1,
		]);
		$this->_Files = null;
		return $File;
	}
	function FileDelete($name) {
		$this->Files();
		if (!isset($this->_FilesAll[$name])) return false;
		$dbFile = $this->_FilesAll[$name];
		D()->page_file->delete(['page_id'=>$this,'name'=>$name]);
		!$dbFile->used() && $dbFile->remove();
		unset($this->_Files[$name]);
		unset($this->_FilesAll[$name]);
		return true;
	}
	function FileHas($name) {
		$this->Files();
		return $this->_Files[$name] ?? false;
	}
	function FilesSort($sort) {
		$i = 1;
		foreach ($sort as $file) {
			D()->page_file->update([
				'page_id' => $this,
				'name' => $file,
				'sort' => $i++
			]);
		}
		$this->_Files = null;
	}

	function classes() {
		if (!isset($this->cache['classes']))
			$this->cache('classes', D()->indexCol($this->sql("SELECT class, 1 FROM ".table('page_class')." WHERE page_id = ".$this)));
		return $this->cache['classes'];
	}
	function hasClass($class) {
		$this->classes();
		return isset($this->cache['classes'][$class]);
	}
	function addClass($class) {
		if (!$this->hasClass($class)) {
			D()->page_class->insert(['page_id'=>$this, 'class'=>$class]);
			$this->cache('classes');
		}
		return $this;
	}
	function removeClass($class) {
		D()->page_class->delete(['page_id'=>$this, 'class'=>$class]);
		$this->cache('classes');
	}

	function Cont($name, $attris=[]) {
		$this->Conts();
		if (!isset($this->Named['c'][$name])) { // && vers::$log === 0: dont create item in the past
			if (!is_array($attris)) $attris = ['module'=>$attris];
			$attris['name'] = $name;
			$this->Named['c'][$name] = $this->createCont($attris);
		}
		return $this->Named['c'][$name];
	}
	function Page($name, $attris=[]) {
		$this->Children();
		if (isset($this->Named['p'][$name])) return $this->Named['p'][$name];
		if (!is_array($attris)) $attris = ['module'=>$attris];
		$attris['name'] = $name;
		return $this->createChild($attris);
	}

	/* xml */
	function fromXml($str) {
		$this->fromXmlNode(simplexml_load_string($str));
	}
	function fromXmlNode($node) {
		if (!$node) return;
		foreach ($node->attributes() as $name => $value) {
		    isset(L::$all[$name]) && $this->Title($name,$value);
			switch ($name) {
              	case 'module':
					if (!Usr()->superuser && !D()->one("SELECT access FROM module WHERE name = ".D()->quote($value))) break;
				case 'online_end':
				case 'online_start':
				case 'visible':
				case 'public': // todo: public="0" not working??
				case 'name':
              		$this->set($name, $value);
              		break;
				case 'class':
              		foreach (explode(' ', $node['class']) as $class) $this->addClass($class);
			}
		}
		 // neu, todo: compacter solution?
		$Children = [];
		foreach ($node->children() as $name => $part) {
		 	$Children[] = ['name'=>$name, 'value'=>$part];
		}
		$Children = array_reverse($Children);
		foreach ($Children as $child) {
			$name = $child['name']; // neu
			$part = $child['value']; // neu
			switch ($name) {
				case 'cont': $Cont = $this->createCont();  break;
				case 'page': $Cont = $this->createChild(); break;
			}
			$Cont->fromXmlNode($part);
		}
	}

	function __toString() { return (string)$this->id; }

	/* beta */
	function firstchild($filter=null) {
		$Children = $this->Children($filter);
		return reset($Children);
	}
	function lastchild($filter=null) {
		$Children = $this->Children($filter);
		return end($Children);
	}
	function previousSibling($filter=null) {
		$Parent = $this->Parent();
		if (!$Parent) return null;
		$Children = $Parent->Children($filter);
		$Next = null;
		$Prev = null;
		while ($C = array_shift($Children)) {
			if ($C === $this) {
				if ($Tmp = array_shift($Children)) {
					$Next = $Tmp;
				}
				break;
			}
			$Prev = $C;
		}
		return $Prev;
	}
	function nextSibling($filter=null) {
		$Parent = $this->Parent();
		if (!$Parent) return null;
		$Children = $Parent->Children($filter);
		$Next = null;
		$Prev = null;
		while ($C = array_shift($Children)) {
			if ($C === $this) {
				if ($Tmp = array_shift($Children)) {
					$Next = $Tmp;
				}
				break;
			}
			$Prev = $C;
		}
		return $Next;
	}

	function Childs(){
		$GLOBALS['skip_stacks'] += 1;
		trigger_error('Page::Childs() deprecated, use Page::Children()');
		$GLOBALS['skip_stacks'] -= 1;
		return $this->Children();
	}
}
