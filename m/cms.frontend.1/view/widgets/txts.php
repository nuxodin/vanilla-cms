<?php namespace qg ?>
<?php if ($Cont->access() < 2) return; ?>

<?php L::nsStart('') ?>
<table id=qgCmsTxtsWindow>
	<?php foreach ($Cont->texts() as $name => $T) { ?>
		<tr>
			<td><?=hee($name)?>&nbsp;
			<td style="width:70%"><div class=-txt cmstxt=<?=$T->id?> contenteditable><?=$T?></div>
	<?php } ?>
</table>
<?php L::nsStop() ?>

<style>
#qgCmsTxtsWindow {
	width:100%;
}
#qgCmsTxtsWindow .-txt {
	max-height:1.9em;
	min-height:1.9em;
	overflow:auto;
	margin:2px;
	margin-bottom:5px;
	padding:6px;
	background:#fff;
	resize:vertical;
	transition: max-height .1s;
	border:1px solid var(--cms-dark);
	outline:none;
}
#qgCmsTxtsWindow .-txt:hover { max-height:6em; }
#qgCmsTxtsWindow .-txt:focus { max-height:30em; border-color:var(--cms-color); }
</style>
