<?php
namespace qg;

qg::on('action', function() {

	$SET = G()->SET['qg'];

	if (QG_HTTPS) {
		if ($_SERVER['SCHEME'] === 'http') {
			header('HTTP/1.1 301');
			header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit;
		}
		if ($SET['HSTS']['max-age']->v) {
			$header = 'Strict-Transport-Security:max-age='.$SET['HSTS']['max-age']->v;
			if ($SET['HSTS']['includeSubDomains']->v) $header .= '; includeSubDomains';
			if ($SET['HSTS']['preload']->v)           $header .= '; preload';
			header($header);
		}
	}

	liveSess::$maxpause = $SET['session']['maxpause']->v;
	liveSess::init();
	L::init();
	liveLog::init();

	// todo: country from ip?
	$l_country = 'CH';
	if (Usr()->is()) {
		$values = Usr()->getVs();
		if (isset($values['country']) && strlen($values['country']) === 2) {
			$l_country = $values['country'];
		}
	}
	$l_country = strtoupper($l_country);
	$winCountryTranslate = [
		'CH' => 's',
		'AT' => 'a',
		'DE' => 'u',
		'IT' => 'a',
		'FR' => 'a',
		'PL' => 'k',
	];
	setLocale(LC_ALL,
		L().'_'.$l_country.'.utf8',
		L().'_'.$l_country.'.UTF-8',
		L().@$winCountryTranslate[$l_country],
		L()
	);
	if (strpos(appRequestUri, 'dbFile/') === 0) {
		dbFile::output(substr(appRequestUri, 7));
		exit;
	}
	$_FILES && File::uploadListener();
	isset($_GET['qgha']) && hashAction::fire($_GET['qgha']);
	if (isset(G()->ASK['serverInterface'])) {
		foreach (G()->ASK['serverInterface'] as $i => $vs)
			$ret['serverInterface'][$i] = Api::call($vs['fn'],$vs['args']);
		Answer($ret);
	}

	/* //todo
	$apiUrl = 'api/v1/';
	$len     = strlen($apiUrl);
	$root    = substr(appRequestUri, 0, $len);
	$apiRequest = substr(appRequestUri, $len);
	if ($root === $apiUrl) {
		$parts = explode('/',$apiRequest);
		$module = $parts[0];
		$file = sysPATH.'/'.$module.'/api.v1.php'; // security?
		is_file($file) && include($file);
		//qg::fire('unanswered_api_v1_request');
		trigger_error('untriggered api v1 request');
		exit;
	}
	*/
});
