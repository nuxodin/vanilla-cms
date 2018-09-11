<?php
namespace qg;

if (!D()->one("SELECT name FROM module WHERE name = 'cms.layout.custom.6'")) {
	D()->query("INSERT INTO module SET access = '1', name = 'cms.layout.custom.6'");
}
if (!is_dir(appPATH.'qg/'.$module)) {
	copyDir(sysPATH.$module.'/custom', appPATH.'qg/'.$module);
	$content .= "/*\n\n";
	$content .= "@font-face {\n\n";
	$content .= "	font-family: 'xxx';\n\n";
	$content .= "	src: url('font/xxx.woff2') format('woff2'), url('font/xxx.woff') format('woff');\n\n";
	$content .= "	font-weight: 400;\n\n";
	$content .= "	font-style: normal;\n\n";
	$content .= "	font-display: fallback;\n\n";
	$content .= "}\n\n";
	$content .= "@media (max-width: 1200px) {}\n\n";
	$content .= "*/\n\n";
	$content .= file_get_contents(sysPATH.'core/css/c1/normalize.css')."\n\n";
	$content .= file_get_contents(sysPATH.'core/css/c1/recommend.css');
	file_put_contents(appPATH.'qg/'.$module.'/pub/base.css', $content);
}
