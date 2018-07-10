<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class html {
	static $head 		= '';
	static $title       = '';
	static $titlePrefix = '';
	static $titleSuffix = '';
	static $content 	= '';
	static $meta        = [];
	static $jsFiles 	= [];
	static $cssFiles 	= [];
	static $optimiseJs  = true;
	static private $jsms = [];
	static function addJSM($v) {
		if (isset(self::$jsms[$v])) return;
		self::$jsms[$v] = [];
	}
	static function _getHeaderJSMs() {
		$ret = '';
		foreach (self::$jsms as $url => $egal) {
			//qg::fire('html::url-jsm',['url'=>&$url]); todo?
			$path = uri2path($url);
			//$mtime = filemtime($path);
			$hash = substr(md5_file($path),0,7);
			$ret .= '<script type=module src="'.$url.'?qgUniq='.$hash.'"></script>'."\n";
		}
		return $ret;
	}

	static function addJSFile($v, $group=null, $compress=true, $mode='') {
		if (isset(self::$jsFiles[$v])) return;
		self::$jsFiles[$v] = ['group'=>$group.$mode, 'file'=>$v, 'compress'=>$compress, 'mode'=>$mode];
	}
	static function addCSSFile($v, $group=null, $compress=true) {
		if (isset( self::$cssFiles[$v] )) return;
		self::$cssFiles[$v] = ['group'=>$group, 'file'=>$v, 'compress'=>$compress];
	}
	static function getHeader() {
		$return  = '<meta charset="utf-8">'."\n";
		$return .= '<title>'.hee(self::$titlePrefix.self::$title.self::$titleSuffix).'</title>'."\n";
		$return .= self::$head;
		foreach (self::$meta as $name => $value) {
			if ($value === '') continue;
			$return .= '<meta name='.hee($name).' content="'.hee($value).'">'."\n";
		}
		$return .= self::_getHeaderCSSFiles();
		if (isset(G()->js_data)) {
			$return .= '<script type=json/c1>'.json_encode(G()->js_data, JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS).'</script>'."\n";
		}
		$return .= self::_getHeaderJSFiles();
		$return .= self::_getHeaderJSMs();
		return $return;
	}
	static function _getHeaderCssFiles() {
		$ret = '';
		$i=0;
		$groups = [];
		foreach (self::$cssFiles AS $item) {
			if (!$item['group']) $item['group'] = $i++;
			$item['path']  = uri2path($item['file']);
			$item['mtime'] = filemtime($item['path']);
			$group = $item['group'];
			$groups[$group][] = $item;
		}
		foreach ($groups as $items) $ret .= self::_getHeaderCSSFilesGroup($items);
		return $ret;
	}
	static function _getHeaderJsFiles() {
		$ret = [''=>'','async'=>'','defer'=>''];
		$i=0;
		$groups = [];
		foreach (self::$jsFiles AS $item) {
			if (!$item['group']) $item['group'] = $i++;
			$item['path']  = uri2path($item['file']);
			$item['mtime'] = filemtime($item['path']);
			$group = $item['group'];
			$groups[$group][] = $item;
		}
		foreach ($groups as $items) {
			$mode = $items[0]['mode'];
			$ret[$mode] .= self::_getHeaderJsFilesGroup($items);
		}
		return $ret[''].$ret['defer'].$ret['async'];
	}
	static function _getHeaderCSSFilesGroup($files) {
		//http://prefixr.com/
		if (debug) {
			$ret = '';
			foreach ($files AS $item) {
				$ret .= '<link rel=stylesheet href="'.$item['file'].'?qgUniq='.$item['mtime'].'">'."\n";
			}
			return $ret;
		}
		$md5 = md5(json_encode($files));
		$md5 = substr($md5,0,11);
		$cFile 	= appPATH.'cache/'.$md5.'.css';
		$cUri 	= appURL .'cache/'.$md5.'.css';
		if (!is_file($cFile)) {
			$str = '';
			foreach ($files AS $item) {
				$base = path2uri(dirname($item['path'])).'/';
				$content = file_get_contents($item['path']);
				$content = self::_modifyCssUrls($content, $base);
				$str .= $item['compress'] ? "\n".self::_compressCss($content) : "\n".$content;
			}
			file_put_contents($cFile, $str);
		}
		return '<link rel=stylesheet href="'.$cUri.'">'."\n";
	}
	static function _getHeaderJSFilesGroup($files) {
		$mode = $files[0]['mode'];
		if (debug || !html::$optimiseJs) {
			$ret = '';
			foreach ($files AS $item) {
				$ret .= '<script src="'.$item['file'].'?qgUniq='.$item['mtime'].'"'.($mode?' '.$mode:'').'></script>'."\n";
			}
			return $ret;
		}
		$md5   = md5(json_encode($files));
		$md5   = substr($md5,0,11);
		$cFile = appPATH.'cache/'.$md5.'.js';
		$cUri  = appURL .'cache/'.$md5.'.js';
		if (!is_file($cFile)) {
			$str = '';
			foreach ($files AS $item) {
				$content = file_get_contents($item['path']);
				$str .= $item['compress'] ? ';'.self::_compressJs($content) : ';'.$content;
			}
			file_put_contents($cFile, $str);
		}
		return '<script src="'.$cUri.'"'.($mode?' '.$mode:'').'></script>'."\n";
	}

	static function _compressJS($str) {
		/* yuicompressor
		$arr = [];
		$x = G()->SET['qg']['binary']['yuicompressor']->v;
		if ($x) {
			exec('java -jar '.$x.' --type js '.uri2path($f), $arr);
			$ret = $arr[0];
		}
		//*/
		// jsmin
		require_once sysPATH.'core/lib/3part/jsmin.php';
		return \JSMin::minify($str);
	}
	static function _compressCss($str) {
		require_once sysPATH.'core/lib/3part/csstidy/class.csstidy.php';
		$css = new \csstidy();
		$css->set_cfg('remove_last_;',true);
		//$css->set_cfg('preserve_css',true);
		$css->load_template('highest_compression');
		$css->parse($str);
		$str = $css->print->plain();

		/*csstidy fix: ff needs font-face format value in '"' */
		$str = preg_replace('/ format\("?([^\")]+)"?\)/',' format("$1")', $str);

		return $str;
	}
	static function _modifyCssUrls($str, $base) {
		return preg_replace_callback('/url\(([^\)]+)\)/', function($matches) use($base) {
			$url = trim($matches[1]);
			$url = preg_replace('/^[\'"]/','',$url);
			$url = preg_replace('/[\'"]$/','',$url);

			if (preg_match('/^(http:|https:|data:image)\//',$url)) {
				return 'url("'.$url.'")';
			} else {
              if ($url[0] === '/') {
  				return 'url("'.$url.'")';
              } else {
                return 'url("'.str_replace('//','/',$base.$url).'")';
              }
			}
		}, $str);
	}

	private static function render() {
		G()->js_data['qgToken']   = qg::token();
		G()->js_data['appURL']    = appURL;
		G()->js_data['sysURL']    = sysURL;
		G()->js_data['c1UseSrc']  = sysURL.'core/js';
		G()->js_data['moduleAge'] = G()->SET['qg']['module_changed']->v;
		return
		"<!DOCTYPE HTML>\n".
		"<html lang=".L().">\n".
		"	<head>".html::getHeader()."\n".
		"	<body>\n".self::$content."\n";
	}
	static function output() {
		header('content-type: text/html; charset=utf-8');
		echo self::render();
	}
}
