<?php
namespace qg;

qg::on('action', function() {
	if (appRequestUri === 'robots.txt') {
		echo G()->SET['cms.backend.webmaster']['robots.txt']->v;
		exit;
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
				/*
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
		exit;
	}
    if (preg_match('/^google/',appRequestUri)) {
		$code = G()->SET['cms.backend.webmaster']['webmaster code google']->v;
		$code = preg_replace( '/^google/', '', $code );
		$code = preg_replace( '/\.html/', '', $code );
		$code = 'google'.$code.'.html';
		if (appRequestUri === $code) {
			echo 'google-site-verification: '.$code;
			exit;
		}
    }
    if (appRequestUri === 'BingSiteAuth.xml' && G()->SET['cms.backend.webmaster']['webmaster code bing']->v) {
		header('content-type: application/xml');
		echo
		"<?xml version=\"1.0\"?>\n".
		"<users>".
		"	<user>".G()->SET['cms.backend.webmaster']['webmaster code bing']->v."</user>".
		"</users>";
        exit;
    }
    if (preg_match('/^yandex_/',appRequestUri) && appRequestUri === 'yandex_'.G()->SET['cms.backend.webmaster']['webmaster code yandex'].'.html') {
        echo
      	'<html>'."\n".
        '    <head>'."\n".
        '        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'."\n".
        '    </head>'."\n".
        '    <body>Verification: '.G()->SET['cms.backend.webmaster']['webmaster code yandex'].'</body>'."\n".
        '</html>'."\n";
        exit;
    }

	// browser fix
	if (!util::ua_is_bot($_SERVER['HTTP_USER_AGENT'])) {
		$uaInfo = util::ua_info($_SERVER['HTTP_USER_AGENT']);
		$browser = $uaInfo['browser'];
		$version = $uaInfo['version'];
		if (   ($browser === 'IE' && $version < 11)
			|| ($browser === 'Safari' && $version < 9.1)
			|| ($browser === 'Firefox' && $version < 52)
			|| ($browser === 'Edge' && $version < 16)
			|| ($browser === 'Chrome' && $version < 65)
			|| ($browser === 'SamsungBrowser' && $version < 6.2)
		) {
			html::addJsFile(sysURL.'cms.backend.webmaster/pub/browser-warning.js',null,true,'async');
		}
	}
});

qg::on('deliverHtml', function() {
	$S = G()->SET['cms.backend.webmaster'];
	$S->getAll();
	if ($S['analytics code google']->v) {

		G()->csp['script-src']['https://www.google-analytics.com'] = true;
		G()->csp['img-src']['https://www.google-analytics.com'] = true;
		G()->csp['img-src']['https://stats.g.doubleclick.net'] = true; // ok?
		G()->csp['connect-src']['https://www.google-analytics.com'] = true;
		G()->csp['connect-src']['https://stats.g.doubleclick.net'] = true;
		html::addJsFile(sysURL.'cms.backend.webmaster/pub/analytics.js',null,null,'async');
		G()->js_data['gAnalytics']['code'] = $S['analytics code google']->v;

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


switch ($_SERVER['REQUEST_URI']) {
	case '/wp-login.php':
	case '/.ftpconfig':
	case '/sftp-config.json':
	case '/.remote-sync.json':
	case '/.vscode/ftp-sync.json':
	case '/homewp-admin/':
	case '/wp-admin/':
	case '//cms/wp-includes/wlwmanifest.xml':
	case '//site/wp-includes/wlwmanifest.xml':
	case '//wp/wp-includes/wlwmanifest.xml':
	case '//wordpress/wp-includes/wlwmanifest.xml':
	case '//blog/wp-includes/wlwmanifest.xml':
	case '//xmlrpc.php?rsd':
	case '//wp-includes/wlwmanifest.xml':
	case '/.git/config':
	case '/js/mage/cookies.js':
	case '/plugins/system/rokbox/':
	case '/test/wp-admin/setup-config.php':
	case '/old/wp-admin/setup-config.php':
	case '/wordpress/wp-admin/setup-config.php':
	case '/wp/wp-admin/setup-config.php':
	case '/user/register':
	case '/index.php?option=com_user&task=register':
	case '/wp-login.php?action=register':
	case '/up.php':
	case '/wp-content/plugins/xaisyndicate/idx.php':
	case '//dbs.php':
	case '//connectors/system/phpthumb.php':
	case '/system/phpthumb.php':
		// trigger_error('suspicious_activity');
	case '/autodiscover/autodiscover.xml':
	case '/.well-known/assetlinks.json':
	case '/apple-app-site-association':
	case '/.well-known/apple-app-site-association':
	case '/readme.txt':
	case '/README.txt':
	case '/license.txt':
	case '/ads.txt';
		header('HTTP/1.0 404 Not Found');
		echo 'not implemented, please contact the website owner';
		exit();
}
