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
$heading = $Cont->SET->make('Heading', '2')->setHandler('select')->setOptions('1', '2', '3', '4')->v;
?>
<div>
	<div class=-contact>
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
			if ($has) { ?>
				<div class=-block>
					<?php
					echo cms_text($Cont, $worker, ['tag'=>'h'.$heading, 'initial' => ['de'=>$worker] ]);
					echo $data['company']??0  ? '<div class=-company>'.$data['company'].'</div>' : '';
					echo $data['name']??0     ? '<div class=-name>'   .$data['name']      .'</div>' : '';
					echo $data['address']??0  ? '<div class=-address>'.$data['address']   .'</div>' : '';
					echo $data['city']??0     ? '<div class=-city>'   .$data['zip'].' '.$data['city'].'</div>' : '';
					echo $data['phone']??0    ? '<div class=-phone>'  .$data['phone']   .'</div>' : '';
					echo $data['email']??0    ? '<div class=-email><a href="mailto:'.hee($data['email']).'">'.$data['email'].'</a></div>' : '';
					echo $data['website']??0  ? '<div class=-website> <a href="'.$data['website'].'" target="_blank" title="'.hee($data['website-title']).'">'.hee($data['website']).'</a></div>' : '';
					?>
				</div>
			<?php }
		}
		?>
		<div class=-block>
			<?php
			echo cms_text($Cont, 'CMS', ['tag'=>'h'.$heading, 'initial' => ['de'=>'Content Management System'] ]);
			echo '<div class=-website> <a href="https://vanilla-cms.org" target=_blank> vanilla CMS | opensource made in switzerland </a></div>';
			echo '<div class=-name>Eine halbe Stunde Schulung und man kann seine Website bedienen</div>';
			?>
		</div>
		<?php if ($Cont->SET['Anderes']['HR-Firmenbezeichnung']->v && $Cont->SET['Anderes']['UID']->v && $Cont->SET['Anderes']['Handelsregisteramt']->v) {
			// $T = $Cont->Text('title_Handelsregistereintrag');
			// if (!trim($T)) $T->get('de')->set('Handelsregistereintrag'); ?>
			<div class=-block>
				<?=cms_text($Cont, 'title_Handelsregistereintrag', ['tag'=>'h'.$heading, 'initial'=>['de'=>'Handelsregistereintrag'] ])?>
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
	</div>
	<div class=-texts>
		<?php include $Cont->modPath.'parts/texts.php' ?>
	</div>
</div>
