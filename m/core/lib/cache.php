<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class cache {
	static $rate = 0.5;
	static $enabled = 1;
	function __construct($path){
		$this->path =& $path;
		$this->_getItem();
		$this->counter = false;
		if (cache::$counters !== null) {
			$counter =& cache::$counters;
			foreach ($path as $part) {
				if (!isset($counter[$part])) $counter[$part] = [];
				$counter =& $counter[$part];
			}
			if (is_array($counter)) $counter = 0;
			$this->counter =& $counter;
		}
	}
	function _getItem() {
		$item =& cache::$data;
		foreach ($this->path as $part) {
			if (!isset($item[$part])) return;
			$item =& $item[$part];
		}
		$this->item =& $item;
	}
	function _createItem() {
		self::$changed = true;
		$item =& cache::$data;
		$path = $this->path; // copy
		$this->index = array_pop($path);
		foreach ($path as $part) {
			if (!isset($item[$part])) $item[$part] = [];
			$item =& $item[$part];
		}
		$this->parent =& $item;
	}
	function get(&$data) { // todo: new get: test!
		if (!self::$enabled) return false;
		if (self::$counters !== null && self::$counters['counter']) {
			$this->counter++;
			$this->_createItem();
			if (($this->counter / self::$counters['counter']) > self::$rate) { // 90 prozent von allen zugriffen
				$this->parent[$this->index] =& $data;
			} else {
				unset($this->parent[$this->index]);
			}
		}
		if (isset($this->item)) {
			$data = $this->item;
			return true;
		} else {
			return false;
		}
	}
	function remove() {
		$this->_createItem();
		unset($this->parent[$this->index]); // remove in the export
		unset($this->item); // remove runtime
		$this->counter = 0;
	}

	static $data = [];
	static $counters = null;
	static $changed = false;

	static function init(){
		self::$data = @unserialize(file_get_contents(appPATH.'qg/qgCacheData.txt'));
		if (rand(0,6) === 1){
			self::$counters = @unserialize(file_get_contents(appPATH.'qg/qgCacheCounters.txt'));
		}
		if (self::$counters !== null) {
			self::$counters['counter'] = isset(self::$counters['counter']) ? self::$counters['counter']+1 : 0;
		}
		//echo '<pre>'; var_dump(self::$data); echo '</pre>';
	}
	static function save(){
		//echo '<pre>'; var_dump(self::$data); echo '</pre>';
		if (self::$changed) {
			file_put_contents(appPATH.'qg/qgCacheData.txt', serialize(self::$data));
		}
		if (self::$counters !== null) {
			file_put_contents(appPATH.'qg/qgCacheCounters.txt', serialize(self::$counters));
		}
	}
}

function cache() {
	return new cache(func_get_args());
}
register_shutdown_function(function(){cache::save();});

cache::init();


/* *
$Cache = cache('testx',3);
//$Cache->remove(); echo 'remove! ';
if (!$Cache->get($data)) {
	 echo 'has not!<br>';
	 $data = 'test data from db'; // sets the cache on shutdown
} else {
	 echo 'has<br>';
}
var_dump($data);
//$Cache->remove();
/* */
