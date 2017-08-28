<?php
namespace qg;

L::nsStart('');
$treeJson = json_encode(Api::call('cms::getTree',[0, ['in'=>$Cont, 'filter'=>(G()->SET['cms.frontend.1']['custom']['tree_show_c']->v?'*':'p')]]));
L::nsStop();
?>
<div class="-standalone qgCmsTreeManager" style="flex:1; margin-bottom:2em" data="<?=hee($treeJson)?>">
	<div class=-h1>
		<span><?=L('Struktur')?></span>
		<input id=cmsPageAddInp style="width:50%" type=text placeholder="<?=hee(L('Neue Unterseite von "###1###"', $Cont->Title()))?>" title="<?=L('Die neue Seite wird als Unterseite der ausgewählten Seite erstellt. Klicken Sie Enter um die Seite zu erstellen')?>" c1-tooltip>
	</div>
	<div id=cmsTreeContainer></div>
</div>
<div class=-standalone>
	<div class=-h1><?=L('Legende')?></div>
	<table class=-padding style="line-height:1">
		<tr>
			<td> <span class=-access-0 style="font-size: 1.7em;">&#x2B24;</span>
			<td> <?=L('Keine Berechtigung')?>
		<tr>
			<td> <span class=-access-1 style="font-size: 1.7em;">&#x2B24;</span>
			<td> <?=L('Seite ansehen')?>
		<tr>
			<td> <span class=-access-2 style="font-size: 1.7em;">&#x2B24;</span>
			<td> <?=L('Seite bearbeiten')?>
		<tr>
			<td> <span class=-access-3 style="font-size: 1.7em;">&#x2B24;</span>
			<td> <?=L('Seite bearbeiten und Berechtigungen verwalten')?>
		<tr>
			<td style="padding-left:2px;"> <span style="font-family: 'qg_cms'; font-size: 1.7em;">&#xe900;</span>
			<td> <?=L('Die Seite ist nicht öffentlich zugänglich')?>
		<tr>
			<td style="padding-left:2px"> <span style="font-family: 'qg_cms'; font-size: 1.7em;">&#xe901;</span>
			<td> <?=L('Die Seite ist terminiert und momentan nicht online')?>
	</table>
</div>
