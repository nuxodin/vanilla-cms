<?php
namespace qg;

class Zip extends \ZipArchive {
	public function open($filename, $flags = null) {
		$this->qgFilename = $filename;
		return parent::open($filename, $flags);
	}
	public function addDir($dir, $localname = null, $regexpExclude = null) {
		if ($localname === null) $localname = basename($dir);
		$iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), true);
		$i = 0;
		foreach ($iter as $file) {
			if ($regexpExclude AND preg_match($regexpExclude, $file)) continue;
			if (++$i > 50) {
				$i = 0;
				$this->close();
				$this->open($this->qgFilename, \ZipArchive::CREATE);
			}
			$path = $localname.substr($file,strlen($dir));
			$path = str_replace('\\', '/', $path);
            $name = preg_replace('/.*\/([^\/]+)$/', '$1', $file);
            if (preg_match('/@eaDir/',$file)) continue;
            if ($name == '..' || $name == '.' || $name == '@eaDir' || $name == 'Thumbs.db') continue;
            if (is_dir($file)) continue;
			$this->addFile($file, $path);
		}
	}
}
