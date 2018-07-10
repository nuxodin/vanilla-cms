<?php
namespace qg;

$LPage = layoutCustom6::layoutPage();
$module = $Cont->vs['module'];

// change layout
if ($LPage->access() > 1) {
    if (isset($vars['saveCustomCss'])) {
        return file_put_contents(appPATH.'qg/cms.layout.custom.6/pub/custom.css', $vars['saveCustomCss']);
    }
    if (isset($vars['deleteImg'])) {
        unlink(appPATH.'qg/cms.layout.custom.6/pub/img/'.$vars['deleteImg']);
    }
    if (isset($vars['getImages'])) {
        $path = appPATH.'qg/'.$module.'/pub/img/';
		!is_dir($path) && mkdir($path);
		$url = path2uri($path);
		$images = [];
		foreach (scanDir($path) as $f) {
			if (is_file($path.$f)) $images[] = $url.$f;
		}
        return $images;
    }
}
