<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

qg::on('cms-ready', function(){
    if (!Page()->edit) return;
    if (isset($_GET['qgCmsNoFrontend'])) return;
    html::addJsFile(sysURL.'cms.image_editor/pub/init.js');
});

qg::on('action', function(){
    if (isset($_FILES['qgDbFileImageEditor'])) {
        $file_id = (int)$_GET['file_id'];
        if (!dbFileImageWritable($file_id)) die('not allowed');
        qg::fire('page::file_upload-before');
        $File = dbFile($file_id);
        $_FILES['qgDbFileImageEditor']['name'] = $File->name(); // dont change file-name
        $File->replaceFromUpload($_FILES['qgDbFileImageEditor']);
        qg::fire('page::file_upload-after');
        exit;
    }
});

class serverInterface_dbFileImageEditor {
    static function getHistory($id){
        $DbFile = dbFile($id);
        $sql =
        " SELECT file.*, log.id as log_id, log.time as log_time ".
        " FROM  ".
        "  _vers_file file ".
        "  LEFT JOIN log ON file._vers_log = log.id ".
        " WHERE ".
        "   file._vers_log ".
        "   AND file.id = ".$id." AND file._vers_space = ".cms_vers::$space.
        " ORDER BY file._vers_log DESC ".
        " LIMIT 40 ";
        $str = '<table style="width:100%">';
        foreach (D()->query($sql) as $row) {
            if (!is_file(appPATH.'qg/file/'.$row['md5'])) continue;
            $old = vers::setLog($row['_vers_log']+1);

            // $_SESSION['cms_vers::log']   = $row['_vers_log'] + 1; // needed?
            // $_SESSION['cms_vers::space'] = $row['_vers_space'];
            dbFile::$All = [];
            $File = dbFile($id)->transform(['w'=>60,'h'=>'40','dpr'=>0,'max'=>true]); // same size as preview (already generated)
            if (!$File->path) continue;
            $out = file_get_contents($File->path);

            $str .= '<tr>';
            $str .=   '<td style="padding:3px 4px 3px 0; width:60px"><img log="'.($row['log_id']).'" style="display:block; margin:auto; border:1px solid black; cursor:pointer" src="data:'.$row['mime'].';base64,'.base64_encode($out).'">';
            $str .=   '<td style="padding:3px 0   3px 0;">'.strftime('%x %H:%M',$row['log_time']);
            if ($Log = D()->log->Entry($row['log_id'])) {
                if ($Log->is() && ($Sess = $Log->Sess())) {
                    if ($Sess->is() && ($Usr = $Sess->Usr())) {
                        $str .= '<br>'.$Usr->firstname.' '.$Usr->lastname;
                    }
                }
            }
            vers::setLog($old); // todo, after loop?
        }
        $str .= '</table>';
        unset($_SESSION['cms_vers::log']);
        unset($_SESSION['cms_vers::space']);
        return $str;
    }
    static function restore($id, $log){
        vers::tableEntriesCopyTo('file', ['id'=>$id], cms_vers::$space, $log, cms_vers::$space);
        unset($_SESSION['cms_vers::log']);
        unset($_SESSION['cms_vers::space']);
    }
    static function getMeta($fileId){
        $vs = dbFile($fileId)->vs;
        return [
            'name' => $vs['name'],
            'vpos' => $vs['vpos'],
            'hpos' => $vs['hpos'],
        ];
    }
    static function setMeta($fileId, $data){
        $allowed = ['name'=>1,'vpos'=>1,'hpos'=>1];
        $nData = ['id'=>$fileId];
        foreach ($data as $name => $value) {
            if (!isset($allowed[$name])) continue;
            $nData[$name] = $value;
        }
        D()->file->update($nData);
        //dbFile($fileId)->setVs($nData);
    }
    static function onBefore($fn, $fileId) {
        if (!dbFileImageWritable($fileId)) return false;
	}
}

function dbFileImageWritable($file_id){
    $file_id = (int)$file_id;
    foreach (D()->query("SELECT * FROM ".vers::view('page_file',cms_vers::$space)." WHERE file_id = ".$file_id) as $row) {
        if (Page($row['page_id'])->access() > 1) return true;
    }
    return false;
}
