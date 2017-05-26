<?php
namespace qg;

$P = cmsBackend::install($module);
$P->Title('en','Webmaster');
$P->Title('de','Webmaster');

G()->SET['cms.backend.webmaster']->make('robots.txt',
	"User-agent: * \n".
	"Disallow: /not_for_searchengines/ \n".
	"# Sitemap: http://".$_SERVER['HTTP_HOST']."/sitemap.xml \n"
);
G()->SET['cms.backend.webmaster']['webmaster code google'];
G()->SET['cms.backend.webmaster']['webmaster code bing'];
G()->SET['cms.backend.webmaster']['webmaster code yandex'];
