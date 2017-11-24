<?php
namespace qg;

qg::on('action', function() {
	if (appRequestUri === 'robots.txt') {
		echo G()->SET['cms.backend.webmaster']['robots.txt']->v;
		exit();
	}
	if (appRequestUri === 'sitemap.xml') {
		header('Content-Type: application/xml');
		echo
		'<?xml version="1.0" encoding="UTF-8"?>'."\n".
		'<urlset '."\n".
		'  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '."\n".
		'  xmlns:xhtml="http://www.w3.org/1999/xhtml" '.
		'> '."\n";
		foreach (Page(1)->Bough(['type'=>'p']) as $P) {
			if ($P->id == 1) continue;
			if (!$P->vs['searchable']) continue;
			if (!$P->isPublic()) continue;
			if (!$P->isOnline()) continue;

			foreach (L::$all as $l) {
				echo
				'  <url>'."\n".
				'    <loc>'.hee(Url('/'.$P->urlSeo($l))).'</loc>'."\n";
		            foreach (L::$all as $altL) { if ($l === $altL) continue;
					  	echo
						'    <xhtml:link rel="alternate" hreflang="'.$altL.'" href="'.hee(Url('/'.$P->urlSeo($altL))).'" />'."\n";
		            }
	            if ($P->vs['log_id_ch']) {
					$Log = D()->log->Entry($P->vs['log_id_ch']);
					if ($Log->is()) {
						echo
						'    <lastmod>'.date('Y-m-d', $Log->time).'</lastmod>'."\n";
					}
	            }
	            /*
	            if ($P->SET['_changefreq']->setHandler('select')->setOptions('always','hourly','daily','weekly','monthly','yearly','never')->v !== '') {
	                 echo '    <changefreq>'.$P->SET['seo_changefreq']->v.'</changefreq>';
	            }
	            if ($P->SET->make('_priority','0.5')->v !== '' && $P->SET->make('seo_priority','0.5')->v !== '0.5') {
	                echo '    <priority>'.str_replace(',','.',(float)$P->SET['seo_priority']->v).'</priority>';
	            }
	            */
	            echo
				'  </url>'."\n";
			}
		}
		echo
		'</urlset>'."\n";
		exit();
	}
    if (preg_match('/^google/',appRequestUri)) {
          $code = G()->SET['cms.backend.webmaster']['webmaster code google']->v;
      	  $code = preg_replace( '/^google/', '', $code );
      	  $code = preg_replace( '/\.html/', '', $code );
          $code = 'google'.$code.'.html';
    	  if (appRequestUri === $code) {
      		echo 'google-site-verification: '.$code;
      		exit();
          }
    }
    if (appRequestUri === 'BingSiteAuth.xml' && G()->SET['cms.backend.webmaster']['webmaster code bing']->v) {
		header('content-type: application/xml');
		echo
		"<?xml version=\"1.0\"?>\n".
		"<users>".
		"	<user>".G()->SET['cms.backend.webmaster']['webmaster code bing']->v."</user>".
		"</users>";
        exit();
    }
    if (preg_match('/^yandex_/',appRequestUri) && appRequestUri === 'yandex_'.G()->SET['cms.backend.webmaster']['webmaster code yandex'].'.html') {
        echo
      	'<html>'."\n".
        '    <head>'."\n".
        '        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'."\n".
        '    </head>'."\n".
        '    <body>Verification: '.G()->SET['cms.backend.webmaster']['webmaster code yandex'].'</body>'."\n".
        '</html>'."\n";
        exit();
    }

	// browser fix
	$ua = $_SERVER['HTTP_USER_AGENT'];
	$ua = preg_replace('/(Mozilla|AppleWebKit)\//', '', $ua);
	$ua = str_replace('Version/', 'Safari/', $ua);
	$ua = preg_replace('/MSIE /', 'IE/', $ua);
	$ua = preg_replace('/ Chrome.*Edge\//', 'Edge/', $ua);
	preg_match('/([a-zA-Z]+)\/([0-9]+\.[0-9])/', $ua, $matches);
	$browser = 'other';
	$version = '1';
	if ($matches) {
		list($x,$browser,$version) = $matches;
		if ($browser =='Trident') {
			$browser = 'IE';
			if (preg_match('/rv:([0-9.]+)/',$ua,$tmp)) $version = $tmp[1];
		}
	}
	if ($browser === 'IE' && $version < 11) {
		html::addJsFile(sysURL.'cms.backend.webmaster/pub/browser-warning.js');
	}
	if ($browser === 'Safari' && $version < 9) {
		html::addJsFile(sysURL.'cms.backend.webmaster/pub/browser-warning.js');
	}
	if ($browser === 'Firefox' && $version < 48) {
		html::addJsFile(sysURL.'cms.backend.webmaster/pub/browser-warning.js');
	}
});

qg::on('deliverHtml', function() {
	$S = G()->SET['cms.backend.webmaster'];
	$S->getAll();
	if ($S['analytics code google']->v) {

		G()->csp['script-src']['https://www.google-analytics.com'] = true;
		G()->csp['img-src']['https://www.google-analytics.com'] = true;
		G()->csp['connect-src']['https://www.google-analytics.com'] = true;
		html::addJsFile(sysURL.'cms.backend.webmaster/pub/analytics.js',null,null,'async');
		G()->js_data['gAnalyticsCode'] = $S['analytics code google']->v;

		// html::$head .=
		// '<script>
		// (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
		// (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		// m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		// })(window,document,\'script\',\'https://www.google-analytics.com/analytics.js\',\'ga\');
		// ga(\'create\', \''.G()->SET['cms.backend.webmaster']['analytics code google']->v.'\', \'auto\');
		// ga(\'send\', \'pageview\');
		// </script>';
	}
	if ($S['html title prefix']->v) {
		html::$titlePrefix = G()->SET['cms.backend.webmaster']['html title prefix']->v;
	}
	if ($S['html title suffix']->v) {
		html::$titleSuffix = G()->SET['cms.backend.webmaster']['html title suffix']->v;
	}
	if (count(L::$all) > 1) {
		//html::$head .= '<link rel=alternate href="'.appURL.'?cmspid='.Page().'" hreflang="x-default">'."\n";
		foreach (L::$all as $l) {
			if ($l === L()) continue;
			html::$head .= '<link rel=alternate href="'.hee(Page()->url($l)).'" hreflang="'.$l.'">'."\n";
		}
	}
});
