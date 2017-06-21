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
		module::syncLocal();
        $index = $this->index();
        $localIndex = module::index();
        foreach (qg::$modules as $module => $egal) { // loop modules in dependency order
            if (!isset($index[$module]))      continue; // not on the server
            if (!isset($localIndex[$module])) { // not local
                trigger_error('should not happen cause we loop local modules!?');
                continue;
            }
            // if ($localIndex[$module]['server'] !== $this->host) {
            //     trigger_error('module ('.$module.') not from this server ('.$this->host.')');
            //     continue;
            // }
            if (versionIsSmaller($localIndex[$module]['version'], $index[$module]['version'])) {
                if (!$this->install($module)) return false;
            }
        }
        return true;
	}
    function install($module) {
        if (!$this->download($module)) return;
        qg::initialize($module);
        return true;
    }
    function download($module) {
        time_limit(600);
        ignore_user_abort(true);
        // download
        $data = $this->indexGet($module);
        if (!$data) return false;
        $tmpZip = appPATH.'cache/tmp/pri/remoteModule.zip';
        if ($this->ftp_user) {
            $this->Ftp()->get($tmpZip, '/module/'.$module.'/'.$data['version'].'.zip');
        } else {
            $options = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
            $content = file_get_contents('https://'.$this->host.'/module/'.$module.'/'.$data['version'].'.zip', false, stream_context_create($options));
            file_put_contents($tmpZip, $content);
        }
        $zip = new Zip;
        if (!$zip->open($tmpZip)) return;
        rrmdir(sysPATH.$module);
        if (is_dir(sysPATH.$module)) return;
        if (!$zip->extractTo(sysPATH)) return;
        $zip->close(); // todo: needed?

        // old, zzz?
        if (function_exists('qg\D')) {
            $E = D()->module->Entry($module)->makeIfNot();
            $E->server        = $this->host;
        }

        $localdata =& module::index()[$module];
        $localdata['server']  = $this->host;
        $localdata['version'] = $data['version'];
        $localdata['updated'] = time();
        module::saveIndex();

        return $data;
    }

    function release($module, $incVersion=2, $notes='') {
        $vs = $this->indexGet($module);
		$v = explode('.',$vs['version']);
		$v = @[(int)$v[0], (int)$v[1], (int)$v[2]];
		foreach ($v as $i => $vp) if ($i >= $incVersion) $v[$i] = 0;
      	isset($v[$incVersion-1]) && ++$v[$incVersion-1];
        return $this->upload($module, implode('.',$v), $notes);
    }
    function upload($module, $version, $notes='') {
        if (!is_dir(sysPATH.$module)) return false;
        time_limit(600);
		$tmpFile = appPATH.'cache/tmp/pri/module_export132s.zip';
		is_file($tmpFile) && unlink($tmpFile);
		$zip = new Zip;
		$zip->open($tmpFile, Zip::CREATE);
		$zip->addDir(sysPATH.$module, null, '/(\.svn)|(zzz)/');
        $zip->close();
        $this->Ftp()->mkdir('/module/');
        $this->Ftp()->mkdir('/module/'.$module.'/');
		$this->Ftp()->put('/module/'.$module.'/'.$version.'.zip', $tmpFile);

        $localdata =& module::index()[$module];
        $localdata['server'] = $this->host;
        $localdata['version'] = $version;
        $localdata['updated'] = time();
        module::saveIndex();

        $data = [
            'version' => $version,
            'size'    => filesize($tmpFile),
            'time'    => time(),
        ];
		$this->indexSet($module, $data);

        $history =& $this->changelog($module);
        $history[] = [
            'version'=> $version,
            'notes'  => $notes,
            'user'   => Usr()->email,
            'time'   => time(),
        ];
        $this->changelogSave($module);

        return $data;
    }
    function delete($module) {
        D()->query("UPDATE module SET server = '' WHERE name = ".D()->quote($module)." AND server = ".D()->quote($this));
        $this->Ftp()->mkdir('/module_deleted');
        @$this->Ftp()->rmdir('/module_deleted/'.$module); // Directory not empty
        $this->Ftp()->rename('/module/'.$module, '/module_deleted/'.$module);
        unset($this->index()[$module]); // works???
        // $this->index(); zzz
        // unset($this->index[$module]);
		$this->_indexSave();
	}

    // changelogs
    var $changelogs = [];
    function &changelog($module){
        if (!isset($this->changelogs[$module])) {
            if ($this->ftp_user) {
                $string = @$this->Ftp()->getString('/module/'.$module.'/changelog.json');
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
    function &index(){
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
    function indexGet($module){
        return $this->index()[$module] ?? null;
    }
    function indexSet($module, $data) {
        $this->index()[$module] = $data;
		$this->_indexSave();
	}
    private function _indexSave() {
        $string = json_encode($this->index, JSON_PRETTY_PRINT);
        $this->Ftp()->putString('/module/index.json', $string);
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
