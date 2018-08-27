<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

class File {
	function __construct($path, $x = null) {
		realpath($path) && ($path = realpath($path));
		$this->path = $path;
	}
	function __toString() {
		return $this->path;
	}
	function touch() {
		trigger_error('used?');
		touch($this->path);
	}
	function read() {
		readfile($this->path);
	}
	function getContents() {
		trigger_error('used?');
		return file_get_contents($this->path);
	}
	function putContents($data) {
		return file_put_contents($this->path, $data);
	}
	function basename($suffix = null) {
		return basename($this->path, $suffix);
	}
	function copyTo($dest) {
		$r = copy($this->path, $dest);
		//$F = new File($dest); // needed????? zzz
		return $r;
	}
	function replaceBy($path) {
		trigger_error('used?');
		$path = is_object($path) ? $path->path : (string)$path;
		$r = copy($path, $this->path);
		$this->csh = null;
		return $r;
	}
	function dirname() {
		trigger_error('used?');
		return dirname($this->path);
	}
	function exists() {
		return is_file($this->path) ? $this : false;
	}
	function mtime() {
		//return @filemtime($this->path); // better?
		return file_exists($this->path) ? filemtime($this->path) : false;
	}
	function size() {
		return filesize($this->path);
	}
	function rename($newname) {
		trigger_error('used?');
		if (rename($this->path, $newname)) {
			$this->path = realpath($newname);
			$this->csh = null;
			return true;
		}
	}
	function unlink() {
		trigger_error('used?');
		if (unlink($this->path)) {
			$this->csh = null;
			return true;
		}
	}
	function extension() {
		return strtolower(preg_replace('/.*\./', '', $this->path));
	}
	function mime() {
		$ext = $this->extension();
		return $ext ? File::extensionToMime($ext) : 'application/octet-stream';
	}
	function url() {
		$url = path2uri($this->path);
		return preg_replace('/\/$/','',$url);
	}
	function uploadTicket($opt = []) {
		if (!isset($this->uploadTicket)) {
			$opt['path'] = $this->path;
			$this->uploadTicket = randString(16);
			$_SESSION['uploadTicket'][$this->uploadTicket] = $opt;
		}
		return $this->uploadTicket;
	}
	function md5() {
		return md5_file($this->path);
	}
	function getText() {
		$ret = '';
		if (!file_exists($this->path)) return '';
		switch ($this->extension()) {
			/*
			case 'todo_pdf':
				if (!G()->SET['qg']['binary']['xpdf']->v) return '';
				$tmpFile = tempnam("","content");
				exec(G()->SET['qg']['binary']['xpdf']  ." -q " . escapeshellarg($this->path)." ".escapeshellarg($tmpFile));
				$ret = file_get_contents($tmpFile);
				unlink($tmpFile);
			break;
			case 'todo_doc':
				if (!G()->SET['qg']['binary']['catdoc']->v) return '';
				exec(G()->SET['qg']['binary']['catdoc']." " . escapeshellarg($this->path), $ret);
				$ret = join("\n", $ret);
			break;
			case 'todo_xls':
				if (!G()->SET['qg']['binary']['xls2csv']->v) return '';
				exec(G()->SET['qg']['binary']['xls2csv']." " . escapeshellarg($this->path), $ret);
				$ret = join("\n", $ret);
			break;
			case 'todo_ppt':
				if (!G()->SET['qg']['binary']['catppt']->v) return '';
				exec(G()->SET['qg']['binary']['catppt']." " . escapeshellarg($this->path), $ret);
				$ret = join("\n", $ret);
			break;
			*/
			case 'csv':
			case 'txt':
				$ret = $this->getContents();
				break;
			case 'jpg':
				if (exif_imagetype($this->path) !== IMAGETYPE_JPEG) break;
				foreach (exif_read_data($this->path, 0, true) as $key => $section) {
				    foreach ($section as $name => $val) {
				    	if (is_array($val)) continue;
				    	$val = trim($val);
				    	if ($val == '') continue;
				    	if ($name === 'html') continue;
				    	if (preg_match('/^UndefinedTag/', $name)) continue;
				    	$ret .= $name.': '.$val."\n";
				    }
				}
			break;
			case 'php':
			case 'htm':
			case 'html':
				$ret = strip_tags($this->getContents());
			break;
		}
		return $ret;
	}

	static function uploadListener() {
		foreach ($_FILES AS $name => $f) {
          	if ($f['size'] === 0) continue;
			if (isset($_SESSION['uploadTicket'][$name])) {
				$opt = $_SESSION['uploadTicket'][$name];
				if (isset($opt['dbfile'])) {
					if ($f['type'] === 'image/pjpeg') { $f['type']='image/jpeg'; }
					$File = dbFile($opt['dbfile']);
					$File->replaceFromUpload($f);
				} else {
					move_uploaded_file($f['tmp_name'], $opt['path']);
				}
			}
		}
	}
	static function extensionToMime($ext) {
		switch ($ext) {
			case 'docm':
				return 'application/vnd.ms-word.document.macroEnabled.12';
			case 'docx':
				return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
			case 'dotm':
				return 'application/vnd.ms-word.template.macroEnabled.12';
			case 'dotx':
				return 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
			case 'potm':
				return 'application/vnd.ms-powerpoint.template.macroEnabled.12';
			case 'potx':
				return 'application/vnd.openxmlformats-officedocument.presentationml.template';
			case 'ppam':
				return 'application/vnd.ms-powerpoint.addin.macroEnabled.12';
			case 'ppsm':
				return 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
			case 'ppsx':
				return 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
			case 'pptm':
				return 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
			case 'pptx':
				return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
			case 'xlam':
				return 'application/vnd.ms-excel.addin.macroEnabled.12';
			case 'xlsb':
				return 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
			case 'xlsm':
				return 'application/vnd.ms-excel.sheet.macroEnabled.12';
			case 'xlsx':
				return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
			case 'xltm':
				return 'application/vnd.ms-excel.template.macroEnabled.12';
			case 'xltx':
				return 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
			case 'dwg':
				return 'application/acad';
			case 'asd':
			case 'asn':
				return 'application/astound';
			case 'tsp':
				return 'application/dsptype';
			case 'dxf':
				return 'application/dxf';
			case 'spl':
				return 'application/futuresplash';
			case 'gz':
				return 'application/gzip';
			case 'ptlk':
				return 'application/listenup';
			case 'hqx':
				return 'application/mac-binhex40';
			case 'mbd':
				return 'application/mbedlet';
			case 'mif':
				return 'application/mif';
			case 'xls':
			case 'xla':
				return 'application/msexcel';
			case 'hlp':
			case 'chm':
				return 'application/mshelp';
			case 'ppt':
			case 'ppz':
			case 'pps':
			case 'pot':
				return 'application/mspowerpoint';
			case 'doc':
			case 'dot':
				return 'application/msword';
			case 'oda':
				return 'application/oda';
			case 'pdf':
				return 'application/pdf';
			case 'ai':
			case 'eps':
			case 'ps':
				return 'application/postscript';
			case 'rtc':
				return 'application/rtc';
			case 'rtf':
				return 'application/rtf';
			case 'smp':
				return 'application/studiom';
			case 'tbk':
				return 'application/toolbook';
			case 'vmd':
				return 'application/vocaltec-media-desc';
			case 'vmf':
				return 'application/vocaltec-media-file';
			case 'htm':
			case 'html':
			case 'shtml':
			case 'xhtml':
				return 'application/xhtml+xml';
			case 'xml':
				return 'application/xml';
			case 'bcpio':
				return 'application/x-bcpio';
			case 'Z':
				return 'application/x-compress';
			case 'cpio':
				return 'application/x-cpio';
			case 'csh':
				return 'application/x-csh';
			case 'dcr':
			case 'dir':
			case 'dxr':
				return 'application/x-director';
			case 'dvi':
				return 'application/x-dvi';
			case 'evy':
				return 'application/x-envoy';
			case 'gtar':
				return 'application/x-gtar';
			case 'hdf':
				return 'application/x-hdf';
			case 'php':
			case 'phtml':
				return 'application/x-httpd-php';
			case 'js':
				return 'application/x-javascript';
			case 'latex':
				return 'application/x-latex';
			case 'bin':
				return 'application/x-macbinary';
			case 'mif':
				return 'application/x-mif';
			case 'nc':
			case 'cdf':
				return 'application/x-netcdf';
			case 'nsc':
				return 'application/x-nschat';
			case 'sh':
				return 'application/x-sh';
			case 'shar':
				return 'application/x-shar';
			case 'swf':
			case 'cab':
				return 'application/x-shockwave-flash';
			case 'spr':
			case 'sprite':
				return 'application/x-sprite';
			case 'sit':
				return 'application/x-stuffit';
			case 'sca':
				return 'application/x-supercard';
			case 'sv4cpio':
				return 'application/x-sv4cpio';
			case 'sv4crc':
				return 'application/x-sv4crc';
			case 'tar':
				return 'application/x-tar';
			case 'tcl':
				return 'application/x-tcl';
			case 'tex':
				return 'application/x-tex';
			case 'texinfo':
			case 'texi':
				return 'application/x-texinfo';
			case 't':
			case 'tr':
			case 'roff':
				return 'application/x-troff';
			case 'man':
			case 'troff':
				return 'application/x-troff-man';
			case 'me':
			case 'troff':
				return 'application/x-troff-me';
			case 'me':
			case 'troff':
				return 'application/x-troff-ms';
			case 'ustar':
				return 'application/x-ustar';
			case 'src':
				return 'application/x-wais-source';
			case 'zip':
				return 'application/zip';
			case 'au':
			case 'snd':
				return 'audio/basic';
			case 'es':
				return 'audio/echospeech';
			case 'tsi':
				return 'audio/tsplayer';
			case 'vox':
				return 'audio/voxware';
			case 'aif':
			case 'aiff':
			case 'aifc':
				return 'audio/x-aiff';
			case 'dus':
			case 'cht':
				return 'audio/x-dspeeh';
			case 'mid':
			case 'midi':
				return 'audio/x-midi';
			case 'mp2':
				return 'audio/x-mpeg';
			case 'ram':
			case 'ra':
				return 'audio/x-pn-realaudio';
			case 'rpm':
				return 'audio/x-pn-realaudio-plugin';
			case 'stream':
				return 'audio/x-qt-stream';
			case 'wav':
				return 'audio/x-wav';
			case 'dwf':
				return 'drawing/x-dwf';
			case 'cod':
				return 'image/cis-cod';
			case 'ras':
				return 'image/cmu-raster';
			case 'fif':
				return 'image/fif';
			case 'gif':
				return 'image/gif';
			case 'ief':
				return 'image/ief';
			case 'jpeg':
			case 'jpg':
			case 'jpe':
				return 'image/jpeg';
			case 'png':
				return 'image/png';
			case 'svg':
				return 'image/svg+xml';
			case 'tiff':
			case 'tif':
				return 'image/tiff';
			case 'mcf':
				return 'image/vasa';
			case 'wbmp':
				return 'image/vnd.wap.wbmp';
			case 'fh4':
			case 'fh5':
			case 'fhc':
				return 'image/x-freehand';
			case 'ico':
				return 'image/x-icon';
			case 'pnm':
				return 'image/x-portable-anymap';
			case 'pbm':
				return 'image/x-portable-bitmap';
			case 'pgm':
				return 'image/x-portable-graymap';
			case 'ppm':
				return 'image/x-portable-pixmap';
			case 'rgb':
				return 'image/x-rgb';
			case 'xwd':
				return 'image/x-windowdump';
			case 'xbm':
				return 'image/x-xbitmap';
			case 'xpm':
				return 'image/x-xpixmap';
			case 'wrl':
				return 'model/vrml';
			case 'csv':
				return 'text/comma-separated-values';
			case 'css':
				return 'text/css';
			case 'htm':
			case 'html':
			case 'shtml':
				return 'text/html';
			case 'js':
			case 'mjs':
				return 'text/javascript';
			case 'txt':
				return 'text/plain';
			case 'rtx':
				return 'text/richtext';
			case 'rtf':
				return 'text/rtf';
			case 'tsv':
				return 'text/tab-separated-values';
			case 'wml':
				return 'text/vnd.wap.wml';
			case 'wmlc':
				return 'application/vnd.wap.wmlc';
			case 'wmls':
				return 'text/vnd.wap.wmlscript';
			case 'wmlsc':
				return 'application/vnd.wap.wmlscriptc';
			case 'xml':
				return 'text/xml';
			case 'etx':
				return 'text/x-setext';
			case 'sgm':
			case 'sgml':
				return 'text/x-sgml';
			case 'talk':
			case 'spc':
				return 'text/x-speech';
			case 'mpeg':
			case 'mpg':
			case 'mpe':
				return 'video/mpeg';
			case 'qt':
			case 'mov':
				return 'video/quicktime';
			case 'viv':
			case 'vivo':
				return 'video/vnd.vivo';
			case 'avi':
				return 'video/x-msvideo';
			case 'movie':
				return 'video/x-sgi-movie';
			case 'webm':
				return 'video/webm';
			case 'vts':
			case 'vtts':
				return 'workbook/formulaone';
			case '3dmf':
			case '3dm':
			case 'qd3d':
			case 'qd3':
				return 'x-world/x-3dmf';
			case 'wrl':
				return 'x-world/x-vrml';
			case 'bin':
			case 'exe':
			case 'com':
			case 'dll':
			case 'class':
				return 'application/octet-stream';
			default:
				return 'application/octet-stream';
		}
	}
}
