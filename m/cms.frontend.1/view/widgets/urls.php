<?php namespace qg ?>
<div class=qgCmsFront1UrlManager pid=<?=$Cont?>>
	<table class="-urls -styled -noborder" style="width:100%">
		<tbody>
			<?php foreach (D()->query($Cont->sql("SELECT * FROM page_url WHERE page_id = '".$Cont."' ")) as $row) { ?>
				<tr data-lang="<?=$row['lang']?>">
					<td> <?=$row['lang']?>
					<td> <input class=-url type=text value="<?=hee($row['url'])?>" style="width:100%">
					<td style="width:1px;"> <input class=-custom type=checkbox <?=$row['custom']?'checked':''?> title=fix>
					<td style="width:1px;"> <input class=-target <?=$row['target']?'checked':''?> type=checkbox title="<?=L('Neues Fenster')?>">
			<?php } ?>
	</table>
	<br>
	<b><?=L('Direktlinks')?></b>
	<table class="-directlinks -styled -noborder" style="width:100%">
		<tbody>
			<tr>
				<td> <input class=-add_inp style="width:100%">
				<td style="width:20px"><button class=-add><?=L('hinzufügen')?></button>
			<?php foreach (D()->query("SELECT * FROM page_redirect WHERE redirect = '".$Cont."' ORDER BY request") as $row) { ?>
			<tr>
				<td><?=hee($row['request'])?><td class=-delete style="cursor:pointer; width:20px"><img src="<?=sysURL?>cms.frontend.1/pub/img/delete.svg" alt="<?=hee(L('löschen'))?>">
			<?php } ?>
	</table>
</div>

<style>
.qgCmsFront1UrlManager .-custom         { display:none;  }
.qgCmsFront1UrlManager .-custom:checked { display:block; }
</style>
