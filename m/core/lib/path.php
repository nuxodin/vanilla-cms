<?php
namespace qg;

class Url {
	function __construct($url = null) {
		if ($url === null) { $url = $_SERVER['SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; }
		$this->url = (string)$url;
		$x = parse_url($url);
		$this->scheme = $x['scheme'] ?? $_SERVER['SCHEME'];
		$this->host   = $x['host'] ?? $_SERVER['HTTP_HOST'];
		$this->path   = $x['path'] ?? '/';
		$this->query  = $x['query'] ?? '';
		$this->hash   = $x['fragment'] ?? '';
	}
	function getURL() {
		return $this->url;
	}
	function scheme() {
		return $this->scheme;
	}
	function host() {
		return $this->host;
	}
	function path() {
		return $this->path;
	}
	function query() {
		return $this->query;
	}
	function addParam($n, $v, $amp = true) {
		$this->stripParam($n);
		$this->query .= ($this->query ? ($amp?'&amp;':'&') : '') .$n.'='.rawurlencode($v);
		return $this;
	}
	function stripParam($n) {
		$this->query = preg_replace('/(\&|&amp;|^)'.preg_quote($n,'/').'[^&]*(\&|$)/', '$2', $this->query);
		return $this;
	}
	function hash() {
		return $this->hash;
	}
	function __toString() {
		return $this->scheme.'://'.$this->host.$this->path.($this->query!==''?'?'.$this->query:'').($this->hash?'#'.$this->hash:'');
	}
	function toPath() {
		if (strpos('./', $this->path) === false) {
			return $_SERVER['DOCUMENT_ROOT'].$this->path;
		} else {
			return realpath($_SERVER['DOCUMENT_ROOT'].$this->path);
		}
	}
	function relativeUrl($url) {
		if (preg_match('/^http/',$url)) {
			$url =  $url;
		} elseif (preg_match('/^\//',$url)) {
			$url = $this->scheme.'://'.$this->host.$url;
		} else {
			$base = preg_replace('/[^\/]*$/','', $this->path);
			$url = $this->scheme.'://'.$this->host.$base.$url;
		}
		return new Url($url);
	}
	function relativeTo($Url) {
		$To = Url($Url);
		if ($this->scheme !== $To->scheme) return (string)$this;
		if ($this->host !== $To->host) return (string)$this;
		if ($this->path !==$To->path) {
			$back = '';
			$base = $To->path;
			while ($base = preg_replace('/\/[^\/]*$/','', $base)) {
				if (strpos($this->path, $base) === 0) {
					break;
				} else {
					$back .= '../';
				}
			}
			$url = $back.substr($this->path,strlen($base)+1);
			return $url;
		}
	}
}

function Url($url = null) {
	return new Url($url);
}

function uri2path($uri) { // deparced use class url
	if (strpos('./', $uri) === false) {
		return $_SERVER['DOCUMENT_ROOT'].$uri;
	} else {
		return realpath($_SERVER['DOCUMENT_ROOT'].$uri);
	}
}
function path2uri($path) {
	if (strpos($path, './') === false) {
		$p = $path;
	} elseif (!( $p = realpath($path) )) {
		$p = $path;
	}
	$host_path 	= substr( $p, strlen( $_SERVER['DOCUMENT_ROOT'] ) ).'/';
	$host_path 	= str_replace( '\\', '/', $host_path );
	$host_path 	= preg_replace('!^[\/]!', '', $host_path);
	$uri = '/'.$host_path; //.'/';
	$uri = str_replace('//', '/', $uri);
	return $uri;
}
