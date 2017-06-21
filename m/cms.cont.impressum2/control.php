<?php
namespace qg;
global $cmsContImpressum2;

$superSET = G()->SET['cms.cont.impressum2'][$Cont->id];

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

call_user_func_array(array($superSET['Design']['Designer']->setHandler('select'),'setOptions'), $designer);
call_user_func_array(array($superSET['Fotografie']['Fotograf']->setHandler('select'),'setOptions'), $photographer);

if ($developer) {
	$superSET['Technische Umsetzung']->make('Firmenname', $developer['Firmenname']);
	$superSET['Technische Umsetzung']->make('Adresse', 	  $developer['Adresse']);
	$superSET['Technische Umsetzung']->make('PLZ/Ort', 	  $developer['PLZ/Ort']);
	$superSET['Technische Umsetzung']->make('Telefon', 	  $developer['Telefon']);
	$superSET['Technische Umsetzung']->make('Mail', 	  $developer['Mail']);
	$superSET['Technische Umsetzung']->make('Website', 	  $developer['Website']);
	$superSET['Technische Umsetzung']->make('title-tag',  $developer['title-tag']);

	$superSET['Konzept']->make('Firmenname', $developer['Firmenname']);
	$superSET['Konzept']->make('Adresse', 	 $developer['Adresse']);
	$superSET['Konzept']->make('PLZ/Ort', 	 $developer['PLZ/Ort']);
	$superSET['Konzept']->make('Telefon', 	 $developer['Telefon']);
	$superSET['Konzept']->make('Mail', 	  	 $developer['Mail']);
	$superSET['Konzept']->make('Website', 	 $developer['Website']);
	$superSET['Konzept']->make('title-tag',  $developer['title-tag']);
}

$Cont->SET['Unternehmen']->make('Firmenname', '');
$Cont->SET['Unternehmen']->make('Adresse',	  '');
$Cont->SET['Unternehmen']->make('PLZ/Ort',	  '');
$Cont->SET['Unternehmen']->make('Telefon',	  '');
$Cont->SET['Unternehmen']->make('Mail',		  '');
$Cont->SET['Unternehmen']->make('Website',	  'auto');
$Cont->SET['Unternehmen']['title-tag'];

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

$superSET['Design']['Andere']['Firmenname'];
$superSET['Design']['Andere']['Adresse'];
$superSET['Design']['Andere']['PLZ/Ort'];
$superSET['Design']['Andere']['Telefon'];
$superSET['Design']['Andere']['Mail'];
$superSET['Design']['Andere']['Website'];

$superSET['Fotografie']['Andere']['Firmenname'];
$superSET['Fotografie']['Andere']['Adresse'];
$superSET['Fotografie']['Andere']['PLZ/Ort'];
$superSET['Fotografie']['Andere']['Telefon'];
$superSET['Fotografie']['Andere']['Mail'];
$superSET['Fotografie']['Andere']['Website'];

$superSET['Technische Umsetzung']->make('Einblenden', 1)->setType('bool');
$superSET['Konzept']             ->make('Einblenden', 0)->setType('bool');
