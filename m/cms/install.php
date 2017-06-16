<?php
namespace qg;

qg::need('fileEditor');
qg::need('cms.cont.flexible');
qg::need('cms.cont.text');

if (!D()->one("SELECT id FROM page WHERE id = 1")) {
	D()->query("INSERT INTO page SET id=1, access=1, visible=1, searchable=1, module = 'cms.layout.custom.6', basis = 0, type='p'");
	Page(1)->Title('en','root');
	Page(1)->Title('de','root');
}

G()->SET['cms']['editmode']->custom();
G()->SET['cms']['models']->custom();
G()->SET['cms']['clipboard']->custom();

$dir = sysPATH.$module.'/locale/';
foreach (scandir($dir) as $file) {
	preg_match('/([a-z]{2})\.json/', $file, $matches);
	if (isset($matches[1])) {
		$lang = $matches[1];
		$json = file_get_contents($dir.$file);
		L::import($lang,'cms', $json );
	}
}
