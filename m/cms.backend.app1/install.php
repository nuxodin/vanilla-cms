<?php
namespace qg;

$P = cmsBackend::install($module);
$P->Title('en', 'App-Configuration');
$P->Title('de', 'App-Konfiguration');


//$SET = G()->SET['m']['cms.app1']; zzz

if (G()->SET['m']->has('cms.app1')) {
    //echo 'migrate app1';
    G()->SET['app1'] = G()->SET['m']['cms.app1'];
    unset(G()->SET['m']['cms.app1']);
}
$SET = G()->SET['app1'];


/* settings */
if (!$SET['name']->v) $SET['name'] = $_SERVER['HTTP_HOST'];
$SET['description'];

/* chrome *
$SET['chrome_permissions']['unlimitedStorage']->setType('bool');
$SET['chrome_permissions']['notifications']->setType('bool');
$SET['chrome_permissions']['geolocation']->setType('bool');
$SET['chrome_permissions']['background']->setType('bool');
$SET['chrome_permissions']['clipboardRead']->setType('bool');
$SET['chrome_permissions']['clipboardWrite']->setType('bool');
/**/
unset($SET['chrome_permissions']); // zzz
/* firefox *
$SET['firefox_permissions']['alarm'];
$SET['firefox_permissions']['backgroundservice'];
$SET['firefox_permissions']['bluetooth'];
$SET['firefox_permissions']['browser'];
$SET['firefox_permissions']['camera'];
$SET['firefox_permissions']['contacts']['access']->setHandler('select')->setOptions('readonly','readwrite','readcreate','createonly');
$SET['firefox_permissions']['desktop-notification'];
$SET['firefox_permissions']['device-storage']['access']->setHandler('select')->setOptions('readonly','readwrite','readcreate','createonly');
$SET['firefox_permissions']['fmradio'];
$SET['firefox_permissions']['geolocation'];
$SET['firefox_permissions']['mobileconnection'];
$SET['firefox_permissions']['power'];
$SET['firefox_permissions']['push'];
$SET['firefox_permissions']['settings']['access']->setHandler('select')->setOptions('readonly','readwrite');
$SET['firefox_permissions']['sms'];
$SET['firefox_permissions']['storage'];
$SET['firefox_permissions']['systemclock'];
$SET['firefox_permissions']['network-http'];
$SET['firefox_permissions']['network-tcp'];
$SET['firefox_permissions']['telephony'];
$SET['firefox_permissions']['wake-lock-screen'];
$SET['firefox_permissions']['webapps-manage'];
$SET['firefox_permissions']['wifi'];
foreach ($SET['firefox_permissions'] as $S) { $S['description']; }
/**/
unset($SET['firefox_permissions']); // zzz
//$SET->make('fullscreen',0)->setType('bool');
unset($SET['fullscreen']); // zzz

if ($SET->has('theme-color')) {
    $SET['theme_color'] = $SET['theme-color'];
    unset($SET['theme-color']);
}


$SET['name'];
$SET['description'];
$SET['short_name'];
$SET['theme_color'];
$SET['background_color'];
$SET->make('display','browser')->setHandler('select')->setOptions('fullscreen','standalone','minimal-ui','browser');
$SET->make('orientation','any')->setHandler('select')->setOptions('any','natural','landscape','portrait','portrait-primary','portrait-secondary','landscape-primary','landscape-secondary');
$SET->make('service-worker',0)->setType('bool');
$SET->make('categories','')->setHandler('textarea');

$SET['use apple-touch-icon']->setType('string')->setHandler('select')->setOptions('','precomposed','not precomposed');
$SET['use apple-mobile-web-app-capable']->setHandler('checkbox');
$SET['apple-mobile-web-app-status-bar-style']->setHandler('select')->setOptions('','black','black-translucent');
$SET['no telefon nr detection']->setHandler('checkbox');
$SET['no start_url']->setType('bool');
$SET->make('tile color', '#aaaaff');

$SET['viewport']->make('width', 'device-width');
$SET['viewport']->make('initial-scale', '');
$SET['viewport']->make('maximum-scale', '');
$SET['viewport']->make('user-scalable', '');


//$SET->make('https only',0)->setType('bool');

G()->SET['app1']['owner']['company'];
G()->SET['app1']['owner']['name'];
G()->SET['app1']['owner']['address'];
G()->SET['app1']['owner']['zip'];
G()->SET['app1']['owner']['city'];
G()->SET['app1']['owner']['email'];
G()->SET['app1']['owner']['phone'];
