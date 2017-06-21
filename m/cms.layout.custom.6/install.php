<?php
namespace qg;

qg::need('cms.layouter3');

if (!D()->one("SELECT name FROM module WHERE name = 'cms.layout.custom.6'")) {
	D()->query("INSERT INTO module SET access = '1', name = 'cms.layout.custom.6'");
}

if (!is_dir(appPATH.'qg/'.$module)) {
	copyDir(sysPATH.$module.'/custom', appPATH.'qg/'.$module);
	$content = "#container {\n";
	$content .= "	-webkit-hyphens:auto;\n";
	$content .= "	-ms-hyphens:auto;\n";
	$content .= "	hyphens:auto;\n";
	$content .= "}\n";
	$content .= "@media (max-width: 1200px) {}\n\n";
	$content .= file_get_contents(sysPATH.'core/js/c1/css/normalize.css')."\n\n";
	$content .= file_get_contents(sysPATH.'core/js/c1/css/recommend.css');
	file_put_contents(appPATH.'qg/'.$module.'/pub/base.css', $content);
}
