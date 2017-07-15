<?php
namespace qg;

class cms_app1 {
	static $iconSizes = [558,270,256,192,180,160,152,144,128,120,114,96,76,72,64,60,57,48,32,16];

	static function bestIcon($size) {
		$iconDims = array_reverse(self::$iconSizes);
		foreach ($iconDims as $dim) {
			if ($dim < $size) continue;
			$File = Page(1)->File('app'.$dim);
			if ($File->exists()) return $File->path;
		}
	}

	static function filledIcon($w, $h=null) {
		if ($h === null) $h = $w;
		$file = self::bestIcon($w);
		if ($file) {
			$Img = new Image($file);
			$corners[] = imagecolorat($Img->Img, 2,           2);
			$corners[] = imagecolorat($Img->Img, $Img->x()-3, 2);
			$corners[] = imagecolorat($Img->Img, $Img->x()-3, $Img->y()-3);
			$corners[] = imagecolorat($Img->Img, 2,           $Img->y()-3);
			foreach ($corners as $corner => $color) {
				$colors[$color] = ($colors[$color] ?? 0) + 1;
			}
			$count = max($colors);
			$color = array_search($count, $colors);
			$Img = $Img->getResized($w, $h, true);
			$Bg = Image::create($w, $h);
			imagealphablending($Bg->Img, false);
			imagesavealpha    ($Bg->Img, true);
			$dst_x = ( $Bg->x() - $Img->x() ) / 2;
			$dst_y = ( $Bg->y() - $Img->y() ) / 2;
			imagefill($Bg->Img, 0, 0, $color);
			imagecopyresampled($Bg->Img, $Img->Img, $dst_x, $dst_y, 0, 0, $Img->x(), $Img->y(), $Img->x(), $Img->y());
			$export = appPATH.'cache/pri/app-icon-filled-'.$w.'-'.$h.'.png';
			imagepng($Bg->Img, $export);
			return $export;
			//dbFile::output($File.'/w-'.$size.'/h-'.$size.'/dpr-0');
		}
	}
}

qg::on('action', function() {
	$SET = G()->SET['app1'];
	$SET->getAll();

	/****************************************
	/****  icons
	/***************************************/
	$outputFile = function($size) {
		$file = cms_app1::filledIcon($size);
		header('content-type: image/png');
		is_file($file) && readfile($file);
		exit();
	};
	if (preg_match('/^app-icon-/', appRequestUri)) {
		$size = (int)substr(appRequestUri, 9);
		$outputFile($size);
	}
	switch (appRequestUri) {
		case 'favicon.ico':
			require __DIR__ . '/class-php-ico.php';
			$destination = appPATH.'cache/pri/app1-favicon.ico1';
			$ico_lib = new \PHP_ICO();
			foreach ([16,32,48] as $size) {
				$file = cms_app1::filledIcon($size,$size);
				$file && $ico_lib->add_image($file, [[$size, $size]]);
			}
			$ico_lib->save_ico($destination);
			header('content-type: image/x-icon');
			is_file($destination) && readfile($destination);
			exit();
		case 'mstile-70x70.png':
			$outputFile(128);
			//For Windows 8 / IE11.
		case 'mstile-144x144.png':
			$outputFile(144);
			//For Windows 8 / IE10.
		case 'mstile-150x150.png':
			$outputFile(270);
			//For Windows 8 / IE11.
		case 'mstile-310x310.png':
			$outputFile(558);
			//For Windows 8 / IE11.
		//case 'mstile-310x150.png':
			//$outputFile(310);
			//For Windows 8 / IE11.
		case 'apple-touch-icon-57x57-precomposed.png':
		case 'apple-touch-icon-57x57.png':
			$outputFile(57);
			//iPhone and iPad users can turn web pages into icons on their home screen. Such link appears as a regular iOS native application. When this happens, the device looks for a specific picture. The 57x57 resolution is convenient for non-retina iPhone with iOS6 or prior. Learn more in Apple docs.
		case 'apple-touch-icon-60x60-precomposed.png':
		case 'apple-touch-icon-60x60.png':
			$outputFile(60);
			//Same as apple-touch-icon-57x57.png, for non-retina iPhone with iOS7.
		case 'apple-touch-icon-72x72-precomposed.png':
		case 'apple-touch-icon-72x72.png':
			$outputFile(72);
			//Same as apple-touch-icon-57x57.png, for non-retina iPad with iOS6 or prior.
		case 'apple-touch-icon-76x76-precomposed.png':
		case 'apple-touch-icon-76x76.png':
			$outputFile(76);
			//Same as apple-touch-icon-57x57.png, for non-retina iPad with iOS7.
		case 'apple-touch-icon-114x114-precomposed.png':
		case 'apple-touch-icon-114x114.png':
			$outputFile(114);
			//Same as apple-touch-icon-57x57.png, for retina iPhone with iOS6 or prior.
		case 'apple-touch-icon-120x120-precomposed.png':
		case 'apple-touch-icon-120x120.png':
			$outputFile(120);
			//Same as apple-touch-icon-57x57.png, for retina iPhone with iOS7.
		case 'apple-touch-icon-144x144-precomposed.png':
		case 'apple-touch-icon-144x144.png':
			$outputFile(144);
			//Same as apple-touch-icon-57x57.png, for retina iPad with iOS6 or prior.
		case 'apple-touch-icon-152x152-precomposed.png':
		case 'apple-touch-icon-152x152.png':
			$outputFile(152);
			//Same as apple-touch-icon-57x57.png, for retina iPad with iOS7.
			//case 'apple-touch-icon-precomposed.png':
		case 'apple-touch-icon-precomposed.png':
			//Same as apple-touch-icon.png, expect that is already have rounded corners (but neither drop shadow nor gloss effect).
		case 'apple-touch-icon.png':
			//Same as apple-touch-icon-57x57.png, for "default" requests, as some devices may look for this specific file. This picture may save some 404 errors in your HTTP logs. See Apple docs
		case 'apple-touch-icon-180x180-precomposed.png':
		case 'apple-touch-icon-180x180.png':
			$outputFile(180);
			// iphone 6
	}

	/****************************************
	/****  favicon
	/***************************************/
	if (appURL !== '/')  html::$head .= '<link rel=icon href="'.appURL.'favicon.ico">'."\n";
	/****************************************
	/****  icons
	/***************************************/
	if ($SET['use icon']->setType('bool')->v) {
		foreach ([192,160,96,32,16] as $size) {
			html::$head .= '<link rel=icon type=image/png href='.appURL.'app-icon-'.$size.'.png sizes='.$size.'x'.$size.">\n";
		}
	}
	/****************************************
	/****  apple-touch-icons
	/***************************************/
	if ($SET['use apple-touch-icon']->v) {
		$precomposed = $SET['use apple-touch-icon']->v === 'precomposed' ? '-precomposed' : '';
		foreach ([180,152,144,120,114,76,72,60,57] as $size) {
			html::$head .= '<link rel=apple-touch-icon'.$precomposed.' sizes='.$size.'x'.$size.' href="'.appURL.'apple-touch-icon-'.$size.'x'.$size.$precomposed.'.png">'."\n";
		}
		//For non-Retina iPhone, iPod Touch, and Android 2.1+ devices:
		html::$head .= '<link rel=apple-touch-icon'.$precomposed.' href="'.appURL.'apple-touch-icon'.$precomposed.'.png">'."\n";
	}
	/****************************************
	/****  Viewport
	/***************************************/
	$arr = [];
	foreach ($SET['viewport'] as $k => $S) {
		if ($S->v) $arr[] = $k.'='.$S;
	}
	if ($arr) html::$meta['viewport'] = implode(', ', $arr);
	/****************************************
	/****  more...
	/***************************************/
	if ($SET['use apple-mobile-web-app-capable']->v) {
		html::$meta['apple-mobile-web-app-capable'] = 'yes';
		html::$meta['mobile-web-app-capable'] = 'yes';
	}
	if ($v = $SET['apple-mobile-web-app-status-bar-style']->v) {
		html::$meta['apple-mobile-web-app-status-bar-style'] = $v;
	}
	if ($SET['no telefon nr detection']->v) {
		html::$meta['format-detection'] = 'telephone=no';
		html::$meta['SKYPE_TOOLBAR'] = 'SKYPE_TOOLBAR_PARSER_COMPATIBLE';
	}
	// ios add to homescreen
	// http://cubiq.org/add-to-home-screen

	// apple-touch-startup-image

	if ($v = $SET['name']->v) {
		html::$meta['apple-mobile-web-app-title'] = $SET['name']->v;
		html::$meta['application-name'] = $SET['name']->v; // ie tile
	}
	if ($v = $SET['theme_color']->v) {
		html::$meta['theme-color'] = $SET['theme_color']->v;
	}

	/****************************************
	/******  w3c webapp manifest
	/****************************************/
	html::$head .= '<link rel=manifest href="'.appURL.'w3c.manifest.json">'."\n";
	G()->csp['manifest-src']["'self'"] = true;
	if (appRequestUri === 'w3c.manifest.json') {
		// header(''); expires header?
		header('Content-Type: application/manifest+json');
		foreach ([16,30,32,48,60,64,90,120,128,256] as $size) {
			$icons[] = [
				'src'   => appURL.'app-icon-'.$size.'.png',
				'sizes' => $size.'x'.$size,
				'type'  => 'image/png',
				//'density' => '1'
			];
		}
		$app = [
			'lang'             => L::$def,
			'name'             => $SET['name']->v,
			'description'      => $SET['description']->v,
			'short_name'       => $SET['short_name']->v,
			'icons'            => $icons,
			'scope'            => (string)Url(appURL),
			'start_url'        => (string)Url(appURL)->addParam('c1Standalone',1),
			'display'          => $SET['display']->v,
			'orientation'      => $SET['orientation']->v,
			'theme_color'      => $SET['theme_color']->v,
			'background_color' => $SET['background_color']->v,
		];
		if ($SET['service-worker']->v) {
			$app['serviceworker'] = [
				'src'       => (string)Url(appURL.'cms.app1.service-worker.js'),
				'scope'     => (string)Url(appURL),
				//'use_cache' => false,
			];
		}
		echo json_encode($app,JSON_PRETTY_PRINT);
		exit();
	}
	/****************************************
	/******  firefox-app
	/****************************************/
	if (appRequestUri === 'manifest.webapp') {
		header('Content-Type: application/x-web-app-manifest+json');
		$app = [
			'name'				=> $SET['name']->v ?: $SET['short_name']->v,
			'description'		=> $SET['description']->v,
			'launch_path'		=> appURL.'?c1Standalone=1',
			'default_locale'	=> L::$def,
			'icons' 			=> [
 				'16'  => appURL.'app-icon-16.png',
				'30'  => appURL.'app-icon-30.png',
				'32'  => appURL.'app-icon-32.png',
				'48'  => appURL.'app-icon-48.png',
				'60'  => appURL.'app-icon-60.png',
				'64'  => appURL.'app-icon-64.png',
				'90'  => appURL.'app-icon-90.png',
				'120' => appURL.'app-icon-120.png',
				'128' => appURL.'app-icon-128.png',
				'256' => appURL.'app-icon-256.png',
			],
			'version' 		=> $SET['version']->v,
			'orientation' 	=> $SET['orientation']->v,
			'fullscreen'	=> isset(['fullscreen'=>1,'standalone'=>1,'minimal-ui'=>1][$SET['display']->v]),
			//'appcache_path'	=> $SET->make('appcache_path','')->v,
			'developer'	=> [
				'name'	=> $SET['developer']['name']->v,
				'url'	=> $SET['developer']['url']->v
			],
		];
		foreach ($SET['firefox_permissions'] as $k => $S) {
			if ($S['description']->v) $app['permissions'][$k] = $S->get();
		}
		echo json_encode($app,JSON_PRETTY_PRINT);
		exit();
	}

	/****************************************
	/****  chrome crx https://developer.chrome.com/webstore/hosted_apps
	/***************************************/
	if (appRequestUri === 'app.crx') {
		$app = [
			'name'			=> $SET['short_name']->v,
			'description'	=> $SET['name']->v,
			'version'		=> $SET['version']->v,
			'app' => [
				'launch' => [
					'web_url' => (string)Url(appURL)->addParam('c1Standalone',1),
				]
			],
			'icons' => [
				'128' => appURL.'app-icon-128.png',
			],
			'manifest_version' => 2
		];
		foreach ($SET['chrome_permissions'] as $k => $S) {
			if ($S->v) $app['permissions'][] = $k;
		}
		$manifest = json_encode($app,JSON_PRETTY_PRINT);
		$zip = new Zip;
		$path = appPATH.'cache/tmp/googleApp.crx';
		$zip->open($path, Zip::CREATE);
		$zip->addFromString('manifest.json', $manifest);
		$zip->close();
		header('Content-Type: application/zip');
		readfile($path);
		exit();
	}

	/****************************************
	/****  ie tile http://www.buildmypinnedsite.com/de-DE
	/***************************************/
	if ($SET['use ie10 tile']->setType('bool')->v) {
		html::$meta['msapplication-TileImage'] = appURL.'mstile-144x144.png';
		html::$meta['msapplication-TileColor'] = $SET['tile color']->v ?: $SET['background_color']->v;
		html::$meta['msapplication-starturl']  = appURL.'?c1Standalone=1';
		//html::$meta['msapplication-window']  = 'width=1024;height=768';
		//html::$meta['msapplication-task']    = 'name=Blog;action-uri=http://app.com/blog;icon-uri=http://app.com/blog.ico';
		html::$meta['msapplication-config'] = appURL.'browserconfig.xml';
		if (appRequestUri === 'browserconfig.xml') {
			$cont =
			'<?xml version="1.0" encoding="utf-8"?>'."\n".
			'<browserconfig>'."\n".
			'    <msapplication>'."\n".
			'        <tile>'."\n".
			'            <square70x70logo   src="'.appURL.'mstile-70x70.png"/>'."\n".
			'            <square150x150logo src="'.appURL.'mstile-150x150.png"/>'."\n".
			'            <wide310x150logo   src="'.Page(1)->File('app558')->url().'/w-558/h-270/img.png"/>'."\n". // todo
			'            <square310x310logo src="'.appURL.'mstile-310x310.png"/>'."\n".
			'            <TileColor>'.($SET['tile color']->v ?: $SET['background_color']->v).'</TileColor>'."\n".
			'        </tile>'."\n".
			//'        <notification>'."\n".
			//'            <polling-uri  src="http://notifications.buildmypinnedsite.com/?feed=http://www.schweizerbauer.ch/rss/vermischtes-p-63.xml&id=1"/>'."\n".
			//'            <polling-uri2 src="http://notifications.buildmypinnedsite.com/?feed=http://www.schweizerbauer.ch/rss/vermischtes-p-63.xml&id=2"/>'."\n".
			//'            <frequency>30</frequency>'."\n".
			//'            <cycle>1</cycle>'."\n".
			//'        </notification>'."\n".
			'    </msapplication>'."\n".
			'</browserconfig>';
			header('Content-Type: text/xml');
			echo $cont;
			exit();
		}
	}

	if (appRequestUri === 'cms.app1.service-worker.js') {
		// cache headers?
		header('content-type: application/javascript');
		readfile(sysPATH.'cms.backend.app1/pub/service-worker.js');
		exit();
	}
	if (appRequestUri === 'cms.app1.offline.html') {
		header('content-type: text/html');
		$SET = G()->SET['app1'];
		$file = cms_app1::filledIcon(256);
		if ($file) {
			$type = pathinfo($file, PATHINFO_EXTENSION);
			$data = file_get_contents($file);
			$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
			$imageTag = '<img src="'.$base64.'" style="width:200px">';
		} else {
			$imageTag = '';
		}
		echo
		'<!doctype html>'.
		'<html lang=en>'.
			'<meta charset=utf-8>'.
			'<title>'.$SET['short_name'].'</title>'.
			'<body style="text-align:center">'.
			'<h2>'.$SET['short_name'].'</h2><h3>is offline</h3>'.
			$imageTag.
			'';
		exit();
	}

});

qg::on('deliverHtml', function(){
	if (G()->SET['app1']['service-worker']->setType('bool')->v) {
		G()->csp['worker-src']["'self'"] = 1;
		html::addJsfile(sysURL.'core/js/c1.js');
		html::addJsfile(sysURL.'cms.backend.app1/pub/sw-register.js');
	}
});
