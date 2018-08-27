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
	static function icon($width, $options=[]){
		$height = $options['height']??$width; // todo if needed
		$padding = $options['padding']??0;
		$padding = max($padding,0);

		$file = self::bestIcon($width-($padding*2));
		if (!$file) return;
		$Img = new Image($file);

		$color = imagecolorallocatealpha($Img->Img, 0, 0, 0, 127); // transparent
		// if ($options['edge-colors']) {
		// 	$corners[] = imagecolorat($Img->Img, 2,           2);
		// 	$corners[] = imagecolorat($Img->Img, $Img->x()-3, 2);
		// 	$corners[] = imagecolorat($Img->Img, $Img->x()-3, $Img->y()-3);
		// 	$corners[] = imagecolorat($Img->Img, 2,           $Img->y()-3);
		// 	foreach ($corners as $corner => $color) {
		// 		$colors[$color] = ($colors[$color] ?? 0) + 1;
		// 	}
		// 	$count = max($colors);
		// 	$color = array_search($count, $colors);
		// }
		$Img = $Img->getResized($width-($padding*2), $height-($padding*2), true);
		$Bg = Image::create($width, $height);
		imagealphablending($Bg->Img, false);
		imagesavealpha    ($Bg->Img, true);
		imagefill($Bg->Img, 0, 0, $color);
		$dst_x = ( $Bg->x() - $Img->x() ) / 2;
		$dst_y = ( $Bg->y() - $Img->y() ) / 2;
		imagecopyresampled($Bg->Img, $Img->Img, $dst_x, $dst_y, 0, 0, $Img->x(), $Img->y(), $Img->x(), $Img->y());
		$export = appPATH.'cache/pri/app-icon-filled-'.$width.'-'.$height.'.png';
		imagepng($Bg->Img, $export);
		return $export;
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
	$outputFile = function($size, $options=[]) {
		$file = cms_app1::icon($size, $options);
		//$file = cms_app1::filledIcon($size);
		header('cache-control: max-age='.(60*60*2));
		header('content-type: image/png');
		is_file($file) && readfile($file);
		exit;
	};
	if (preg_match('/^app-icon-/', appRequestUri)) {
		$size = (int)substr(appRequestUri, 9);
		$padding = floor(($size-48)/8);
		$outputFile($size, ['padding'=>$padding]);
	}
	switch (appRequestUri) {
		case 'favicon.ico':
			require __DIR__ . '/class-php-ico.php';
			$destination = appPATH.'cache/pri/app1-favicon.ico1';
			$ico_lib = new \PHP_ICO();
			foreach ([16,32,48] as $size) {
				$file = cms_app1::icon($size);
				$file && $ico_lib->add_image($file, [[$size, $size]]);
			}
			$ico_lib->save_ico($destination);
			header('content-type: image/x-icon');
			is_file($destination) && readfile($destination);
			exit;
		case 'mstile-70x70.png': $outputFile(128, ['padding'=>14]); //For Windows 8 / IE11.
		//case 'mstile-144x144.png': $outputFile(144, ['padding'=>70]); //For Windows 8 / IE10. //zzz?
		case 'mstile-150x150.png': $outputFile(270, ['padding'=>82]); //For Windows 8 / IE11.
		case 'mstile-310x310.png': $outputFile(558, ['padding'=>120]); //For Windows 8 / IE11.
		//case 'mstile-310x150.png': //$outputFile(310); //For Windows 8 / IE11.
		// case 'apple-touch-icon-57x57-precomposed.png': case 'apple-touch-icon-57x57.png': $outputFile(57);
		// case 'apple-touch-icon-60x60-precomposed.png': case 'apple-touch-icon-60x60.png': $outputFile(60);
		// case 'apple-touch-icon-72x72-precomposed.png': case 'apple-touch-icon-72x72.png': $outputFile(72);
		// case 'apple-touch-icon-76x76-precomposed.png': case 'apple-touch-icon-76x76.png': $outputFile(76);
		// case 'apple-touch-icon-114x114-precomposed.png': case 'apple-touch-icon-114x114.png': $outputFile(114);
		// case 'apple-touch-icon-120x120-precomposed.png': case 'apple-touch-icon-120x120.png': $outputFile(120);
		// case 'apple-touch-icon-144x144-precomposed.png': case 'apple-touch-icon-144x144.png': $outputFile(144);
		// case 'apple-touch-icon-152x152-precomposed.png': case 'apple-touch-icon-152x152.png': $outputFile(152);
		case 'apple-touch-icon-precomposed.png': case 'apple-touch-icon.png': case 'apple-touch-icon-180x180-precomposed.png': case 'apple-touch-icon-180x180.png': $outputFile(180);
		case 'apple-touch-icon-192x192-precomposed.png': case 'apple-touch-icon-192x192.png': $outputFile(192);
	}

	/****************************************
	/****  favicon
	/***************************************/
	if (appURL !== '/')  html::$head .= '<link rel=icon href="'.appURL.'favicon.ico">'."\n"; // better rel="shortcut icon" ?
	/****************************************
	/****  icons
	/***************************************/
	if ($SET['use icon']->setType('bool')->v) {
		//foreach ([558,192,160,96,32,16] as $size) {
		foreach ([192,160,96,48] as $size) {
			html::$head .= '<link rel=icon type=image/png href='.appURL.'app-icon-'.$size.'.png sizes='.$size.'x'.$size.">\n";
		}
		/****************************************
		/****  apple-touch-icons
		/***************************************/
		$precomposed = $SET['use apple-touch-icon']->v === 'precomposed' ? '-precomposed' : '';
		// 57px iPhone(first generation or 2G), iPhone 3G, iPhone 3GS
		// 76px iPad and iPad mini @1x
		// 120px (retina) iPhone 4, iPhone 4s, iPhone 5, iPhone 5c, iPhone 5s, iPhone 6, iPhone 6s, iPhone 7, iPhone 7s, iPhone8
		// 128px Android Devices Normal Resolution
		// 152px iPad and iPad mini @2x
		// 167px iPad Pro
		// 180px iPhone X, iPhone 8 Plus, iPhone 7 Plus, iPhone 6s Plus, iPhone 6 Plus
		// 192px Android Devices High Resolution
		//foreach ([192,180,167,152,144,128,120,114,76,72,60,57] as $size) {
		foreach ([192] as $size) {
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
		if ($S->v!=='') $arr[] = $k.'='.$S;
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
	html::$head .= '<link rel=manifest href="'.appURL.L().'.app1.webmanifest">'."\n";
	G()->csp['manifest-src']["'self'"] = true;

	if (preg_match('/^([a-z][a-z])\.app1\.webmanifest/',appRequestUri,$matches)) {
		$lang = $matches[1]; // todo
		header('cache-control: no-cache'); // ok?
		header('content-type: application/manifest+json; charset=utf-8');
		// https://docs.microsoft.com/en-us/microsoft-edge/progressive-web-apps/microsoft-store (minimum 512px)
		//foreach ([16,30,32,48,60,64,90,120,128,192,256,512,558] as $size) {
		foreach ([48,192,256,512] as $size) {
			$icons[] = [
				'src'   => appURL.'app-icon-'.$size.'.png',
				'sizes' => $size.'x'.$size,
				'type'  => 'image/png',
				//'density' => '1'
			];
		}
		$app = [
			'lang'             => $lang,
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
			'categories'       => explode("\n", $SET['categories']->v),
		];
		if ($SET['no start_url']->v) unset($app['start_url']);
		foreach ($app as $name => $value) if ($value === '' || $value === [0=>'']) unset($app[$name]);

		if ($SET['service-worker']->v) {
			$app['serviceworker'] = [
				'src'       => (string)Url(appURL.'cms.app1.service-worker.js'),
				'scope'     => (string)Url(appURL),
				//'use_cache' => false,
			];
		}
		echo json_encode($app,JSON_PRETTY_PRINT);
		exit;
	}

	/****************************************
	/****  ie tile http://www.buildmypinnedsite.com/de-DE
	/***************************************/
	if ($SET['use ie10 tile']->setType('bool')->v) {
		//html::$meta['msapplication-TileImage'] = appURL.'mstile-144x144.png';
		html::$meta['msapplication-TileColor'] = $SET['tile color']->v ?: $SET['background_color']->v;
		//html::$meta['msapplication-window']  = 'width=1024;height=768';
		//html::$meta['msapplication-task']    = 'name=Blog;action-uri=http://app.com/blog;icon-uri=http://app.com/blog.ico';
		html::$meta['msapplication-starturl']  = appURL.'?c1Standalone=1';
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
			'    </msapplication>'."\n".
			'</browserconfig>';
			header('Content-Type: text/xml');
			echo $cont;
			exit;
		}
	}

	if (appRequestUri === 'cms.app1.service-worker.js') {
		header('content-type: text/javascript');
		header('Cache-Control: max-age=0');
		readfile(sysPATH.'cms.backend.app1/pub/service-worker.js');
		exit;
	}
	if (appRequestUri === 'cms.app1.offline.html') {
		header('content-type: text/html');
		$SET = G()->SET['app1'];
		$file = cms_app1::icon(256);
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
		exit;
	}

});

qg::on('deliverHtml', function(){
	if (G()->SET['app1']['service-worker']->setType('bool')->v) {
		G()->csp['worker-src']["'self'"] = 1;
		html::addJsfile(sysURL.'core/js/c1.js');
		html::addJsfile(sysURL.'cms.backend.app1/pub/sw-register.js');
	}
});
