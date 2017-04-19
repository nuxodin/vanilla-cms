<?php
namespace qg;

qg::need('cms.layouter3');

if (!D()->one("SELECT id FROM module WHERE name = 'cms.layout.custom.6'")) {
	D()->query("INSERT INTO module SET access = '1', name = 'cms.layout.custom.6'");
}

if (!is_dir(appPATH.'qg/'.$module)) {
	copyDir(sysPATH.$module.'/custom', appPATH.'qg/'.$module);
	$content = "@media (max-width: 1200px) {} \n";
	$content .= file_get_contents(sysPATH.'core/js/c1/css/normalize.css')."\n\n";
	$content .= file_get_contents(sysPATH.'core/js/c1/css/recommend.css');
	file_put_contents(appPATH.'qg/'.$module.'/pub/base.css', $content);
}
