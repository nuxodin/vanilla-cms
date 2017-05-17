<?php
namespace qg;

$P = cmsBackend::install($module);
$P->Title('de', 'App-Konfiguration');


$SET = G()->SET['m']['cms.app1'];

/* settings */
if (!$SET['name']->v) {$SET['name'] = $_SERVER['HTTP_HOST'];}
$SET['description'];
if (!$SET['version']->v) {$SET['version'] = 1;}

/* chrome */
$SET['chrome_permissions']['unlimitedStorage']->setType('bool');
$SET['chrome_permissions']['notifications']->setType('bool');
$SET['chrome_permissions']['geolocation']->setType('bool');
$SET['chrome_permissions']['background']->setType('bool');
$SET['chrome_permissions']['clipboardRead']->setType('bool');
$SET['chrome_permissions']['clipboardWrite']->setType('bool');

/* firefox */
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

$SET->make('fullscreen',0)->setType('bool');

$SET['use apple-touch-icon']->setType('string')->setHandler('select')->setOptions('','precomposed','not precomposed');
$SET['use apple-mobile-web-app-capable']->setHandler('checkbox');
$SET['apple-mobile-web-app-status-bar-style']->setHandler('select')->setOptions('','black','black-translucent');
$SET['no telefon nr detection']->setHandler('checkbox');
$SET->make('tile color', '#aaaaff');

$SET['viewport']->make('width', 'device-width');
$SET['viewport']->make('initial-scale', 1);
$SET['viewport']->make('maximum-scale', 2);
$SET['viewport']->make('user-scalable', 'yes');

//$SET->make('https only',0)->setType('bool');
