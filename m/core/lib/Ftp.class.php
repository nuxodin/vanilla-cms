<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class Ftp {
	function __construct($host, $user, $pass) {
		$this->res = ftp_connect($host);
		$this->connected = ftp_login($this->res, $user, $pass);
		ftp_pasv($this->res, true);
	}
	function get($local, $remote, $mode = null) {
		if ($mode === null) $mode = FTP_BINARY;
		return ftp_get($this->res, $local, $remote, $mode);
	}
	function put($remote, $local, $mode = null) {
		if ($mode === null) $mode = FTP_BINARY;
		return ftp_put($this->res, $remote, $local, $mode);
	}
	function mkdir($name) {
		return @ftp_mkdir($this->res, $name);
	}
	function close() {
		return ftp_close($this->res);
	}
	function rename($old, $new) {
		return ftp_rename($this->res, $old, $new);
	}
	function rmdir($dir) {
		return ftp_rmdir($this->res, $dir);
	}
	function rawlist($dir = '/', $rec = false) {
		return ftp_rawlist($this->res, $dir, $rec);
	}
	function delete($path){
 		return ftp_delete($this->res, $path);
	}
	function getString($remote) {
		$temp = fopen('php://temp', 'r+');
		if (!ftp_fget($this->res, $temp, $remote, FTP_BINARY, 0)) return false;
		rewind($temp);
		return stream_get_contents($temp);
	}
	function putString($remote, $string) {
		$fp = fopen('php://temp', 'r+');
		fputs($fp, $string);
		rewind($fp);
		return ftp_fput($this->res, $remote, $fp, FTP_BINARY);
	}
	function listdir($dir) {
		$rows = $this->rawlist($dir);
		if ($rows===false) return false;
		$items = [];
		foreach ($rows as $row) {
            list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time'], $item['name']) = preg_split("/\s+/", $row);
            $item['type'] = $item['rights']{0} === 'd' ? 'directory' : 'file';
            $items[$item['name']] = $item;
        }
		return $items;
	}
}
