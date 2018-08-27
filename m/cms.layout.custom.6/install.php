<?php
namespace qg;

if (!D()->one("SELECT name FROM module WHERE name = 'cms.layout.custom.6'")) {
	D()->query("INSERT INTO module SET access = '1', name = 'cms.layout.custom.6'");
}
if (!is_dir(appPATH.'qg/'.$module)) {
	copyDir(sysPATH.$module.'/custom', appPATH.'qg/'.$module);
	$content .= "@media (max-width: 1200px) {}\n\n";
	$content .= file_get_contents(sysPATH.'core/css/c1/normalize.css')."\n\n";
	$content .= file_get_contents(sysPATH.'core/css/c1/recommend.css');
	file_put_contents(appPATH.'qg/'.$module.'/pub/base.css', $content);
}
