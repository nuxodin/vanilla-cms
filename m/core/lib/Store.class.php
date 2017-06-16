<?php
namespace qg;

class Store {
    private $_Ftp = null;
    private $_document = false;

    function __construct($host, $ftp_user=null, $ftp_pass=null) {
        $this->host     = $host;
        $this->ftp_user = $ftp_user;
        $this->ftp_pass = $ftp_pass;
    }
    function autoUpdate() {
		// module::sync(); needed?
        $index = $this->indexAll();
        foreach (qg::$modules as $name => $egal) { // loop modules in depency order
            if (!isset($index[$name])) continue; // not on the server
            $E = D()->module->Entry($name);
            if (!$E->is() || !$E->local_version) continue; // needed? should not happen....
            if (versionIsSmaller($E->local_version, $index[$name]['version'])) {
                if (!$this->install($name)) return;
            }
        }
        return true;
	}
    function install($name) {
        if (!$this->download($name)) return;
        qg::install($name);
        return true;
    }
    function download($name) {
        time_limit(600);
        ignore_user_abort(true);
        // download
		$data = $this->indexGet($name);
        if (!$data) return false;
        $tmpZip = appPATH.'cache/tmp/pri/remoteModule.zip';
        if ($this->ftp_user) {
            $this->Ftp()->get($tmpZip, '/module/'.$name.'/'.$data['version'].'.zip');
        } else {
            $options = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
            $content = file_get_contents('https://'.$this->host.'/module/'.$name.'/'.$data['version'].'.zip', false, stream_context_create($options));
            file_put_contents($tmpZip, $content);
        }
		$zip = new Zip;
		if (!$zip->open($tmpZip)) return;
		rrmdir(sysPATH.$name);
        if (is_dir(sysPATH.$name)) return;
		if (!$zip->extractTo(sysPATH)) return;
        $zip->close(); // todo: needed?

        // old
        if (function_exists('qg\D')) {
			$E = D()->module->Entry($name)->makeIfNot();
			$E->server        = $this->host;
			$E->local_version = $data['version'];
			$E->local_updated = time();
        }

        // neu
        $file = sysPATH.'index.json';
        $localdata = json_decode(file_get_contents($file), true) ?: [];
        $localdata[$name]['server'] = $this->host;
        $localdata[$name]['version'] = $data['version'];
        $localdata[$name]['updated'] = time();
        file_put_contents($file, json_encode($localdata,JSON_PRETTY_PRINT));

		return $data;
    }

    function release($name, $incVersion=2, $notes=''){
        $vs = $this->indexGet($name);
		$v = explode('.',$vs['version']);
		$v = @[(int)$v[0], (int)$v[1], (int)$v[2]];
		foreach ($v as $i => $vp) if ($i >= $incVersion) $v[$i] = 0;
      	isset($v[$incVersion-1]) && ++$v[$incVersion-1];
        return $this->upload($name, implode('.',$v), $notes);
    }
    function upload($name, $version, $notes='') {
        if (!is_dir(sysPATH.$name)) return false;
        time_limit(600);
		$tmpFile = appPATH.'cache/tmp/pri/module_export132s.zip';
		is_file($tmpFile) && unlink($tmpFile);
		$zip = new Zip;
		$zip->open($tmpFile, Zip::CREATE);
		$zip->addDir(sysPATH.$name, null, '/(\.svn)|(zzz)/');
        $zip->close();
        $this->Ftp()->mkdir('/module/');
        $this->Ftp()->mkdir('/module/'.$name.'/');
		$this->Ftp()->put('/module/'.$name.'/'.$version.'.zip', $tmpFile);
        if (function_exists('qg\D')) {
			$E = D()->module->Entry($name)->makeIfNot();
			$E->server = $this->host;
			$E->local_version = $version;
			$E->local_updated = time();
			//$E->local_time = time();

			//D()->module->ensure([
            //    'server'        => $this->host,
            //    'name'          => $name,
            //    'local_version' => $version,
        	//	//'local_time'    => time(),
            //    'local_updated' => time(),
            //]);
        }
        $data = [
            'version' => $version,
            'size'    => filesize($tmpFile),
            'time'    => time(),
        ];
		$this->indexSet($name, $data);

        $history =& $this->changelog($name);
        $history[] = [
            'version'=> $version,
            'notes'  => $notes,
            'user'   => Usr()->email,
            'time'   => time(),
        ];
        $this->changelogSave($name);

        return $data;
    }
    function localDelete($name) {
        $data = D()->row("SELECT * FROM module WHERE name = ".D()->quote($name)." AND server = ".D()->quote($this));
        if (!$data) return;
        rrmdir(sysPATH.$name);
        D()->query("DELETE FROM module WHERE name = ".D()->quote($name)." AND server = ".D()->quote($this));
        qg::setInstalled($name, false);
    }
    function serverDelete($name) {
        D()->query("UPDATE module SET server = '' WHERE name = ".D()->quote($name)." AND server = ".D()->quote($this));
        $this->Ftp()->mkdir('/module_deleted');
        @$this->Ftp()->rmdir('/module_deleted/'.$name); // not working, needed?
        $this->Ftp()->rename('/module/'.$name, '/module_deleted/'.$name);
        $this->indexDelete($name);
	}

    // changelogs
    var $changelogs = [];
    function &changelog($module){
        if (!isset($this->changelogs[$module])) {
            if ($this->ftp_user) {
                $string = $this->Ftp()->getString('/module/'.$module.'/changelog.json');
            } else {
                $options = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
                $string = file_get_contents('https://'.$this->host.'/module/'.$module.'/changelog.json', false, stream_context_create($options));
            }
            $this->changelogs[$module] = (array)json_decode($string,true);
        }
        return $this->changelogs[$module];
    }
    function changelogSave($module) {
        $string = json_encode($this->changelog($module), JSON_PRETTY_PRINT);
        $this->Ftp()->putString('/module/'.$module.'/changelog.json', $string);
    }

    // index
    private $index = null;
    function indexGet($module){
        return $this->indexAll()[$module] ?? null;
    }
    function &indexAll(){
        if (!isset($this->index)) {
            if ($this->ftp_user) {
                $string = $this->Ftp()->getString('/module/index.json');
            } else {
                $options = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
                $string = file_get_contents('https://'.$this->host.'/module/index.json', false, stream_context_create($options));
            }
            $this->index = (array)json_decode($string,true);
        }
        return $this->index;
    }
    function indexSet($module, $data) {
        $this->indexAll()[$module] = $data;
		$this->_indexSave();
	}
    function indexDelete($module) {
        $this->indexAll();
        unset($this->index[$module]);
		$this->_indexSave();
	}
    private function _indexSave() {
        $string = json_encode($this->index, JSON_PRETTY_PRINT);
        $this->Ftp()->putString('/module/index.json', $string);
        // zzz v5 compatibility
        $document = new \domDocument();
        $document->appendChild($document->createElement('modules'));
        foreach ($this->index as $module => $data) {
            $node = $document->createElement($module);
        	$document->documentElement->appendChild($node);
            foreach ($data as $name => $value) {
                $node->setAttribute($name, $value);
            }
        }
		$string = preg_replace('/>\s*</',"><", $document->saveXML());
        $this->Ftp()->putString('/module/index.xml', $string);
    }
    // ftp
    function Ftp(){
        if (!$this->_Ftp) $this->_Ftp = new Ftp($this->host, $this->ftp_user, $this->ftp_pass);
        return $this->_Ftp;
    }
	function __toString(){
		return $this->host;
	}
}
