<?php
namespace qg;
global $cmsContImpressum2;

$designer     = ['ausblenden', 'andere'];
$photographer = ['ausblenden', 'andere'];
$developer    = null;

if (is_array($cmsContImpressum2)) {
	foreach ($cmsContImpressum2 as $company => $values) {
		if ($values['designer'])     $designer[]     = $company;
		if ($values['photographer']) $photographer[] = $company;
		if ($values['developer'])    $developer      = $values;
	}
}

foreach (['Kontaktadresse', 'Technische Umsetzung', 'Konzept', 'Design', 'Fotografie'] as $worker) {
	$Cont->SET[$worker]->make('company',      '');
	$Cont->SET[$worker]->make('name',	      '');
	$Cont->SET[$worker]->make('address',      '');
	$Cont->SET[$worker]->make('zip',	      '');
	$Cont->SET[$worker]->make('city',	      '');
	$Cont->SET[$worker]->make('phone',	      '');
	$Cont->SET[$worker]->make('email',	      '');
	$Cont->SET[$worker]->make('website',      '');
	$Cont->SET[$worker]->make('website-title','');
}

$Cont->SET['Anderes']['HR-Firmenbezeichnung'];
$Cont->SET['Anderes']['UID'];
$Cont->SET['Anderes']['Handelsregisteramt'];

$Cont->SET['Einblenden']->make('Haftungsausschluss',    			1)->setType('bool');
$Cont->SET['Einblenden']->make('Haftung für Links',     			1)->setType('bool');
$Cont->SET['Einblenden']->make('Urheberrecht',						1)->setType('bool');
$Cont->SET['Einblenden']->make('Datenschutzerklärung',				1)->setType('bool');
$Cont->SET['Einblenden']->make('Cookies', 							1)->setType('bool');
$Cont->SET['Einblenden']->make('Datenschutz: Facebook', 			0)->setType('bool');
$Cont->SET['Einblenden']->make('Datenschutz: Twitter', 				0)->setType('bool');
$Cont->SET['Einblenden']->make('Datenschutz: Google Adsense', 		0)->setType('bool');
$Cont->SET['Einblenden']->make('Datenschutz: Google Analytics', 	0)->setType('bool');
$Cont->SET['Einblenden']->make('Datenschutz: Google Plus', 			0)->setType('bool');
$Cont->SET['Einblenden']->make('Datenschutz: Google Remarketing',   0)->setType('bool');
