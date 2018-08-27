<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

function dbFile($id, $vs = null) {
	$id = (string)$id;
	dbFile::$All[$id] ?? (dbFile::$All[$id] = new dbFile($id, $vs));
	return dbFile::$All[$id];
}

class dbFile extends File {
	public static $All = [];
	public $path = null;
	public function __construct($id, $vs = null) {
		$this->id = (int)$id;
		$this->vs = $vs === null ? D()->row("SELECT * FROM ".table('file')." WHERE id = ".$this->id) : $vs; // todo, attribut "text" nicht von db holen?
        if ($this->vs) $this->path = appPATH.'qg/file/'.$this->vs['md5'];
	}
	function setVs($vs){
		D()->file->update($this->id, $vs);
		if (!$this->vs) return; // ok?
		$this->vs = $vs + $this->vs;
	}
	function url(){
		return appURL.'dbFile/'.$this.'/u-'.substr($this->vs['md5'],0,4); // todo: include vpos, hpos, so the url it is unique to the delevered content
	}
	function access($set = null) {
		if ($set === null) {
			$access = $this->vs['access'] == '1';
			qg::fire('dbFile::access',  ['File'=>$this, 'access'=>&$access]);
			qg::fire('dbFile::access2', ['File'=>$this, 'access'=>&$access]); // slow
			return $access;
		}
		$this->setVs(['access'=>(int)(bool)$set]);
	}
	function name($set = null) {
		if ($set === null) return $this->vs['name'];
		$this->setVs(['name'=>$set]);
	}
	function extension() {
		return strtolower(preg_replace('/.*\./', '', $this->vs['name']));
	}
	function mime() {
		return $this->vs['mime'];
	}
	function uploadTicket($opt = []) {
		$opt['dbfile'] = $this->id;
		return parent::uploadTicket($opt);
	}
	function updateDb() {
		$this->path = appPATH.'qg/file/'.$this->vs['md5'];
		$this->setVs(['text'=>$this->getText(), 'size'=>$this->size()]);
	}
	function __toString() {
		return (string)$this->id;
	}
	function used(){
		foreach (D()->file->Children() as $Field) {
			$count = D()->one("SELECT count(*) FROM ".table($Field->Table)." WHERE ".$Field." = ".(int)$this->id);
			if ($count) return true;
		}
		$used = false;
		qg::fire('dbFile-used', ['dbFile'=>$this, 'used' => &$used]);
		return $used;
	}
	function remove() {
		D()->file->delete($this);
		$prevent = false;
		qg::fire('dbFile-remove-fs', ['dbFile'=>$this, 'prevent' => &$prevent]);
		if ($prevent) return;
		!D()->one("SELECT id FROM file WHERE md5 = ".D()->quote($this->vs['md5'])) && unlink($this->path); // better in db-delete-event
	}
	function replaceBy($path) {
		$F = new File($path);
 		if (preg_match('/^https?:\/\//',$path)) {
			// not very beautiful
			stream_context_set_default(['ssl'=> ['verify_peer'=>false,'verify_peer_name'=>false]]); // allow files from https
			$basename = $F->basename();
			$content = file_get_contents($path);  // bad: fill ram with content...
			foreach ($http_response_header as $header) { // get filename (Content-Disposition-header)
				$name_value = explode(':',$header,2);
				if (!isset($name_value[1])) continue;
				$name  = trim(strtolower($name_value[0]));
				$value = trim($name_value[1]);
				if ($name === 'content-disposition') {
					preg_match('/filename="([^"]+)"/', $value, $matches);
					if ($matches[1]) {
						$basename = $matches[1];
						break;
					}
				}
			}
			$tmp = appPATH.'cache/tmp/'.preg_replace('/\?.*/','',$basename);
			$F = new File($tmp);
			$F->putContents($content);
		}
		$md5 = $F->md5();
		$this->path = appPATH.'qg/file/'.$md5;
		$F->copyTo($this->path); // old file is not deleted, maybe use in other workspace!
		$this->setVs([
			'name' => $F->basename(),
			'mime' => $F->mime(),
			'text' => $F->getText(),
			'md5'  => $md5,
			'size' => filesize($this->path), // not $F->size(), cause it can be a url!
		]);
	}
	function replaceFromUpload($f) {  // old file is not deleted, maybe use in other workspace!
		$md5 = md5_file($f['tmp_name']);
		$this->path = appPATH.'qg/file/'.$md5;
		move_uploaded_file($f['tmp_name'], $this->path);
		$ext = strtolower(preg_replace('/.*\./', '', $f['name']));
		$type = $f['type'] === 'application/octet-stream' ? File::extensionToMime($ext) : $f['type'];
		$type = preg_replace('/;.*/', '', $type);
		$this->setVs([
			'name' => $f['name'],
			'mime' => $type,
			'md5'  => $md5,
			'size' => $this->size(),
			'text' => $this->getText(),
		]);
	}
	function clone($to=null) {
		$data = $this->vs;
		if ($to===null) {
			unset($data['id']);
			$to = D()->file->insert($data);
		} else {
			$data['id'] = (string)$to;
			D()->file->update($data);
			unset(dbFile::$All[(string)$to]); // remove cache
		}
		return dbFile($to);
	}
	function transform($param) {
		if ($type = Image::able($this->path)) {
			if ($type==='image/gif' && image::is_gif_animated($this->path) /* 0.5 ms*/ ) {
				return $this;
			}
			$w = (int)($param['w'] ?? 0);
			$h = (int)($param['h'] ?? 0);

			$dpr = $_SERVER['HTTP_DPR'] ?? $_COOKIE['q1_dpr'] ?? 1; // todo: move to ::output()
			$dpr = round($dpr,1);
			if ($dpr > 1) {
				if ($param['dpr'] ?? G()->SET['qg']['dbFile_dpr_dependent']->v) {
					$w *= (float)$dpr;
					$h *= (float)$dpr;
				}
			}
			$w    = min($w,9000);
			$h    = min($h,9000);
			$q    = (int)($param['q'] ?? 77);
			$max  = (bool)($param['max'] ?? false);
			$vpos = (float)($param['vpos'] ?? $this->vs['vpos'] ?? 20); // file->vpos not in core module!
			$hpos = (float)($param['hpos'] ?? $this->vs['hpos'] ?? 50);
			$zoom = (float)($param['zoom'] ?? 0);
			$type = str_replace('image/', '', $this->mime());

			$unique = implode([$this->vs['md5'], $w,$h,$q,$max,$vpos,$hpos,$zoom,$type]);

			$nFile = new File(appPATH.'cache/pri/dbfile_img_'.md5($unique));
			if (!$nFile->exists() || $this->mtime() > $nFile->mtime()) {
				$Img = new Image($this->path);
				if ($w == 0 && $h == 0) $w = $Img->x();

				// prevent enlarge
				$oldW = $w;
				$oldH = $h;
				$w = min($Img->x(), $w);
				$h = min($Img->y(), $h);

				if ($max || $h == 0 || $w == 0) {
					$Img = $Img->getResized($w, $h, true);
				} else {
					image::makeProportional($oldW, $oldH, $w, $h);
					$Img = $Img->getAutoCroped($w, $h, $vpos, $hpos, $zoom);
				}

				// old:
				//$Img->saveAs($nFile->path, $type, $q);

				// new:
				if (Image::has_alpha($this->path)) {
					$Img->saveAs($nFile->path, 'png', 9);
					$type = 'png';
				} else {
					$Img->saveAs($nFile->path.'.jpg', 'jpeg', $q);
					$Img->saveAs($nFile->path.'.png', 'png', 9);

					if (filesize($nFile->path.'.jpg') > filesize($nFile->path.'.png')) {
						rename($nFile->path.'.png', $nFile->path);
						unlink($nFile->path.'.jpg');
						$type = 'png';
					} else {
						rename($nFile->path.'.jpg', $nFile->path);
						unlink($nFile->path.'.png');
						$type = 'jpeg';
					}
				}

			}
			$mime = 'image/'.$type;
			return $nFile;
			// header("Pragma: public"); // Emails!?
			// Pragma is the HTTP/1.0 implementation and cache-control is the HTTP/1.1 implementation of the same concept.
			// header("Pragma: private"); // required, why
		}
		return $this;
	}
	static function add($path=null) {
		$dbFile = dbFile(D()->file->insert());
		$path && $dbFile->replaceBy($path);
		return $dbFile;
	}
	static function output($request) {
		$x = explode('/', $request);
		$id = (int)array_shift($x);
		$name = array_pop($x);
		$param = [];
		foreach ($x AS $value) {
			$y = explode('-',$value,2);
			$param[$y[0]] = $y[1] ?? true;
		}
		qg::fire('file_ouput-before');
		$File = $RequestedFile = dbFile($id);
		if (!$File->exists()) {
			header('HTTP/1.1 404 Not Found');
			exit;
		}
		if (!$File->access()) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
		header('HTTP/1.1 200 OK');

		// Header
		$mime = $File->mime() ?: File::extensionToMime($File->extension());
		if ($mime==='image/svg+xml') $mime .= '; charset=utf-8';
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $File->mtime()) .' GMT');
		$expires = time()+60*60*24*180;
 		header('Expires: ' . gmdate('D, d M Y H:i:s', $expires) .' GMT');
		//header('Cache-Control: store, cache, max-age='.$expires.', must-revalidate');
		//header('Cache-Control: store, cache, max-age='.$expires.', private'); // zzz
		header('Cache-Control: max-age='.$expires.', private, immutable');
		header('Pragma: private'); // needed or els it will not cache

		$File = $File->transform($param);
		// header('Content-DPR: '.$dpr); // todo

		if (preg_match('/\.pdf$/', $name) || $File->mime() == 'application/pdf') {
			$mime = 'application/pdf';
			header('Content-Disposition: inline; filename="'.$RequestedFile->name().'";');
			header('Expires: 0');
			//header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Cache-Control: must-revalidate'); // why?
			//header('Pragma: public');
		}
		if (isset($param['dl'])) {
			$mime = 'application/force-download';
			//header('Pragma: public'); // required!? old ie?
			header('Expires: 0');

			// header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			// header('Cache-Control: private', false); // required for certain browsers, old ie?
			header('Cache-Control: private, must-revalidate'); // why?

			header('Content-Disposition: attachment; filename="'.$RequestedFile->name().'";');
			header('Content-Transfer-Encoding: binary');
		}
		if (isset($param['as'])) {
			if ($param['as'] === 'text') $mime = 'text/plain';
		}
		header('Content-Type: '.$mime);

		ob_end_flush();
		session_write_close(); // useful? also close db-connection?

		$etag = 'qg'.$File->mtime();
		if (!isset($_SERVER['HTTP_IF_NONE_MATCH']) || $_SERVER['HTTP_IF_NONE_MATCH'] !== $etag) {
			header('ETag: '.$etag);
			/* rangeDownload http://mobiforge.com/developing/story/content-delivery-mobile-devices */
			header('Content-Length: '.$File->size());
			//header('X-Accel-Redirect: '. $File->path); header('Content-Length: 0 ???'); exit;
			$File->read();
		} else {
			header("HTTP/1.1 304 Not Modified");
		}
	}
}
