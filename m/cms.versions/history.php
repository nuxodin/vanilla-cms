<?php
namespace qg;

$onmodify = function($e) {

    if (!isset($e['Page'])) return; // insert

    foreach ($e['Page']->Path() as $Cont) {
        $data = [
            'page_id'        => $Cont->id,
            'space'          => vers::$space,
            'changed_inside' => time(),
        ];
        if ($Cont === $e['Page'])        $data['changed']      = time();
        if ($Cont->in($e['Page']->Page)) $data['changed_page'] = time();
        D()->vers_cms_page_changed->ensure($data);
    }
};
qg::on('page::modify-before',       $onmodify);
qg::on('page::file_upload-before',  $onmodify);


qg::on('vers::createSpace',function($e){
    D()->query(
    " INSERT vers_cms_page_changed ".
    " SELECT page_id, ".$e['space']." as space, changed_inside, changed_page, changed ".
    " FROM vers_cms_page_changed ".
    " WHERE space = 0");
});




qg::on('Api::after',function($e){ // $fn, &$args, &$ret;
    if (!G()->SET['cms.versions']['draftmode']->v) return;

    if (substr($e['fn'],0,6) === 'page::') {
        $pid = (int)$e['args'][0];
        D()->one("SELECT changed FROM vers_cms_page_changed WHERE space = ".cms_vers::$space." AND page_id = ".$pid);

        $versions = D()->indexCol('SELECT space, unix_timestamp(changed_page) FROM vers_cms_page_changed WHERE page_id = '.$pid);
		if (!isset($versions[0]) || $versions[1] > $versions[0]) { // no live or draft younger then live
            G()->Answer['cms_vers_changed'][$pid] = true;
		}
        $versions = D()->indexCol('SELECT space, unix_timestamp(changed_page) FROM vers_cms_page_changed WHERE page_id = '.Page($pid)->Page);
		if (!isset($versions[0]) || $versions[1] > $versions[0]) { // no live or draft younger then live
            G()->Answer['cms_vers_changed'][Page($pid)->Page->id] = true;
		}
    }
});
