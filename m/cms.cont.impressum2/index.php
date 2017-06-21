<?php
namespace qg;

global $cmsContImpressum2;
/*
$cmsContImpressum2 = [
	'<handler>' => [
		'Firmenname'   => '...',
		'Adresse' 	   => '...',
		'PLZ/Ort' 	   => '...',
		'Telefon' 	   => '...',
		'Mail' 		   => '...',
		'Website' 	   => '...',
		'title-tag'    => '...',
		'designer'     => true/false,
		'photographer' => true/false,
		'developer'    => true/false,
	],
	...
];
*/

$superSET = G()->SET['cms.cont.impressum2'][$Cont->id];

$printData = function($data) {
	echo '<div class=-name>'.($data['Firmenname']??'').'</div>';
	echo $data['Adresse']??0 ? '<div class=-address>'.$data['Adresse'].'</div>' : '';
	echo $data['PLZ/Ort']??0 ? '<div class=-city>'   .$data['PLZ/Ort'].'</div>' : '';
	echo $data['Telefon']??0 ? '<div class=-phone>'  .$data['Telefon'].'</div>' : '';
	echo $data['Mail']??0    ? '<div class=-mail><a href="mailto:'.$data['Mail'].'">'.$data['Mail'].'</a></div>' : '';
	echo $data['Website']??0 ? '<div class=-web> <a href="'.$data['Website'].'" target="_blank" title="'.$data['title-tag'].'">'.$data['Website'].'</a></div>' : '';
};

$tag = $Cont->SET['Heading']->v;
?>
<div>
	<?php if ($Cont->SET['Unternehmen']['Firmenname']->v && $Cont->SET['Unternehmen']['PLZ/Ort']->v && $Cont->SET['Unternehmen']['PLZ/Ort']->v && $Cont->SET['Unternehmen']['Telefon']->v) {
		$data = $Cont->SET['Unternehmen']->get();
		if ($data['Website'] === 'auto') $data['Website'] = $_SERVER['SCHEME'].'://'.$_SERVER['HTTP_HOST'];

		$T = $Cont->Text('title_Unternehmen');
		if (!trim($T)) $T->get('de')->set('Kontaktadresse'); ?>
		<div class="-contact -owner">
			<h<?=$tag.' '.($Cont->edit? 'contenteditable cmstxt='.$T->id : '')?>><?=$T?></h<?=$tag?>>
			<?php $printData($data); ?>
		</div>
	<?php } ?>

	<?php if ($superSET['Konzept']['Einblenden']->v) {
		$data = $superSET['Konzept']->get();

		$T = $Cont->Text('title_Konzept');
		if (!trim($T)) $T->get('de')->set('Konzept'); ?>
		<div class="-contact -concept">
			<h<?=$tag.' '.($Cont->edit? 'contenteditable cmstxt='.$T->id : '')?>><?=$T?></h<?=$tag?>>
			<?php $printData($data); ?>
		</div>
	<?php } ?>

	<?php if ($superSET['Design']['Designer']->v !== 'ausblenden' && $superSET['Design']['Designer']->v !== '') {
		if ($superSET['Design']['Designer']->v !== 'andere' && is_array($cmsContImpressum2)) {
			$company = $superSET['Design']['Designer']->v;
			$data = $cmsContImpressum2[$company];
		} else {
			$data = $superSET['Design']['Andere']->get();
		}
		$T = $Cont->Text('title_Design');
		if (!trim($T)) $T->get('de')->set('Design'); ?>
		<div class="-contact -design">
			<h<?=$tag.' '.($Cont->edit? 'contenteditable cmstxt='.$T->id : '')?>><?=$T?></h<?=$tag?>>
			<?php $printData($data); ?>
		</div>
	<?php } ?>

	<?php if ($superSET['Fotografie']['Fotograf']->v !== 'ausblenden' && $superSET['Fotografie']['Fotograf']->v !== '') {
		if ($superSET['Fotografie']['Fotograf']->v !== 'andere' && is_array($cmsContImpressum2)) {
			$company = $superSET['Fotografie']['Fotograf']->v;
			$data = $cmsContImpressum2[$company];
		} else {
			$data = $superSET['Fotografie']['Andere']->get();
		}
		$T = $Cont->Text('title_Fotografie');
		if (!trim($T)) $T->get('de')->set('Fotografie'); ?>
		<div class="-contact -photos">
			<h<?=$tag.' '.($Cont->edit? 'contenteditable cmstxt='.$T->id : '')?>><?=$T?></h<?=$tag?>>
			<?php $printData($data); ?>
		</div>
	<?php } ?>

	<?php if ($superSET['Technische Umsetzung']['Einblenden']->v) {
		$data = $superSET['Technische Umsetzung']->get();
		$T = $Cont->Text('title_TechnischeUmsetzung');
		if (!trim($T)) $T->get('de')->set('Technische Umsetzung / CMS'); ?>
		<div class="-contact -development">
			<h<?=$tag.' '.($Cont->edit? 'contenteditable cmstxt='.$T->id : '')?>><?=$T?></h<?=$tag?>>
			<?php $printData($data); ?>
		</div>
	<?php } ?>

	<?php if ($Cont->SET['Anderes']['HR-Firmenbezeichnung']->v && $Cont->SET['Anderes']['UID']->v && $Cont->SET['Anderes']['Handelsregisteramt']->v) {
		$T = $Cont->Text('title_Handelsregistereintrag');
		if (!trim($T)) $T->get('de')->set('Handelsregistereintrag'); ?>
		<div class="-contact -traderegister">
			<h<?=$tag.' '.($Cont->edit? 'contenteditable cmstxt='.$T->id : '')?>><?=$T?></h<?=$tag?>>
			<table border="0">
			    <tr>
					<td style="width:200px">Eingetragener Firmenname:
					<td><?=$Cont->SET['Anderes']['HR-Firmenbezeichnung']->v?>
				<tr>
					<td>UID:
					<td><?=$Cont->SET['Anderes']['UID']->v?>
				<tr>
					<td>Handelsregisteramt:
					<td><?=$Cont->SET['Anderes']['Handelsregisteramt']->v?>
			</table>
		</div>
	<?php } ?>
	<div class=-texts>
		<?php include $Cont->modPath.'parts/texts.php' ?>
	</div>
</div>
