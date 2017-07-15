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
$tag = $Cont->SET['Heading']->v;
?>
<div>
	<?php

	foreach (['Kontaktadresse', 'Technische Umsetzung', 'Konzept', 'Design', 'Fotografie'] as $worker) {
		$data = $Cont->SET[$worker]->get();
		$has = $data['company'] || $data['name'];
		// from app1
		if (!$has && $worker==='Kontaktadresse') {
			$data = G()->SET['app1']['owner']->get();
			$data['website'] = $Cont->SET[$worker]['website']->v ?: $_SERVER['SCHEME'].'://'.$_SERVER['HTTP_HOST'];
			$data['website-title'] = $Cont->SET[$worker]['website-title']->v ?: 0;
			$has = $data['company'] || $data['name'];
		}
		if ($has) {
			$T = $Cont->Text($worker);
			if (!trim($T)) $Cont->Text($worker, 'de', $worker); ?>
			<div class="-contact -owner">
				<h<?=$tag.' '.($Cont->edit? 'contenteditable cmstxt='.$T->id : '')?>><?=$T?></h<?=$tag?>>
				<?php
				echo $data['company']??0  ? '<div class=-company>'.$data['company'].'</div>' : '';
				echo $data['name']??0     ? '<div class=-name>'   .$data['name']      .'</div>' : '';
				echo $data['address']??0  ? '<div class=-address>'.$data['address']   .'</div>' : '';
				echo $data['city']??0     ? '<div class=-city>'   .$data['zip'].' '.$data['city'].'</div>' : '';
				echo $data['phone']??0    ? '<div class=-phone>'  .$data['phone']   .'</div>' : '';
				echo $data['email']??0    ? '<div class=-email><a href="mailto:'.$data['email'].'">'.$data['email'].'</a></div>' : '';
				echo $data['website']??0  ? '<div class=-website> <a href="'.$data['website'].'" target="_blank" title="'.hee($data['website-title']).'">'.hee($data['website']).'</a></div>' : '';
				?>
			</div>
		<?php }
	}
	?>
	<?php if ($Cont->SET['Anderes']['HR-Firmenbezeichnung']->v && $Cont->SET['Anderes']['UID']->v && $Cont->SET['Anderes']['Handelsregisteramt']->v) {
		$T = $Cont->Text('title_Handelsregistereintrag');
		if (!trim($T)) $T->get('de')->set('Handelsregistereintrag'); ?>
		<div class="-contact -traderegister">
			<h<?=$tag.' '.($Cont->edit? 'contenteditable cmstxt='.$T->id : '')?>><?=$T?></h<?=$tag?>>
			<table>
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
