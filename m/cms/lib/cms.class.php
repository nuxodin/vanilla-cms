<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class cms {
	static $MainPage = null;
	static $RequestedPage = null;
	static $modules = null;
	static $layouts = null;
	static $RenderPath = [];
	static $_Pages = [];

	static function Page($id = 0, $vs = 0) {
		$id = (int)(string)$id;
		return self::$_Pages[$id] ?? new Page($id, $vs);
	}
	static function PagesByModule($module) {
		$ret = [];
		foreach (D()->query("SELECT * FROM ".table('page')." WHERE module = '".$module."'") as $vs) {
			$ret[$vs['id']] = self::Page($vs['id'], $vs);
		}
		return $ret;
	}
	static function PageByModule($module) {
		$ret = self::PagesByModule($module);
		if (!$ret) return false;
		return array_shift($ret);
	}
	static function PageFromRequest() {
		$pid = $_GET['cmspid'] ?? D()->one("SELECT page_id FROM ".table('page_url')." WHERE url = ".D()->quote(appRequestUri));
		return Page((int)$pid);
	}
	static function render() {
		$Page = self::PageFromRequest();
		if (!$Page->is()) {
			// search for redirect
			if ($redirect = D()->one("SELECT redirect FROM page_redirect WHERE request = ".D()->quote(appRequestUri))) {
				if (is_numeric($redirect)) {
					$url = $_SERVER['SCHEME'].'://'.$_SERVER['HTTP_HOST'].Page($redirect)->url();
				} else {
					$url = $redirect;
				}
				header('HTTP/1.1 301'); // better 302 (temporary) ?
				header('Location: '.$url);
				exit();
			}
			// not found
			header("HTTP/1.1 404 Not Found");
			$Page = Page( G()->SET['cms']['pageNotFound']->v );
		}
		self::$MainPage = self::$RequestedPage = $Page;
		if (!self::$MainPage->access()) { // no access
			header("HTTP/1.1 401 Unauthorized");
			self::$MainPage = Page(G()->SET['cms']['pageNoAccess']->v);
		}
		if (!self::$MainPage->isReadable()) { // offline
			header("HTTP/1.1 401 Unauthorized");
			self::$MainPage = Page(G()->SET['cms']['pageOffline']->v);
		}
		qg::fire('deliverHtml'); // deprecated
		//qg::fire('cms::output-before'); // todo
		html::$content .= self::$MainPage->get();
		qg::fire('cms-ready'); // deprecated
		//qg::fire('cms::output-after'); // todo
		$tpl = new template();
		echo $tpl->get(appPATH.'qg/html-template.php');
	}
	static function getModules() {
		if (self::$modules === null) {
			self::$modules = [];
			$sql =
			" SELECT m.name, count(page.id) as num 					" .
			" FROM module m LEFT JOIN page ON m.name = page.module	" .
			" WHERE m.name LIKE 'cms.cont.%'			" .
			" GROUP BY m.name										" .
			" ORDER BY count(page.id) DESC							";
			foreach (D()->query($sql) as $vs) {
				self::$modules[$vs['name']] = sysPATH.$vs['name'].'/';
			}
		}
		return self::$modules;
	}
	static function getLayouts() {
		if (self::$layouts === null) {
			self::$layouts = [];
			$sql =
			" SELECT m.name, count(page.id) as num 					" .
			" FROM module m LEFT JOIN page ON m.name = page.module	" .
			" WHERE m.name LIKE 'cms.layout.%'		" .
			" GROUP BY m.name										" .
			" ORDER BY count(page.id) DESC							";
			foreach (D()->query($sql) as $vs) {
				self::$layouts[$vs['name']] = sysPATH.$vs['name'].'/';
			}
		}
		return self::$layouts;
	}
	static function filter($Pages, $filter) {
		$filter = (array)$filter;
		if (!isset($filter['type'])) {
			$filter['type'] = 'p';
		}
		$ret = [];
		foreach ($Pages AS $id => $C) {
			if (isset($filter['type']) && $filter['type'] !== '*') {
				if ($C->vs['type'] !== $filter['type']) continue;
			}
			if (isset($filter['visible'])) {
				if ((bool)$C->vs['visible'] !== (bool)$filter['visible']) continue;
			}
			if (isset($filter['module'])) {
				$filter['module'] = (array)$filter['module'];
				if (!in_array($C->vs['module'], $filter['module'])) continue;
			}
			if (isset($filter['access'])) {
				if ($C->access() < $filter['access']) continue;
			}
			if (in_array('navi', $filter)) {
				if (!$C->vs['visible'] || !$C->isReadable() || !(trim($C->Title()) || $C->edit)) {
					continue;
				}
			}
			if (in_array('access', $filter)) {
				if (!$C->access()) continue;
			}
			if (in_array('readable', $filter)) {
				if (!$C->isReadable()) continue;
			}
			$ret[$id] = $C;
		}
		return $ret;
	}

	static $classes_expose_css = null;
	static function classesExposeCss () {
		if (self::$classes_expose_css === null) {
			self::$classes_expose_css = json_decode( G()->SET['cms']['classes_expose_css']->v, 1);
		}
		return self::$classes_expose_css;
	}
}
