<?php namespace qg ?>
<div>
	<?php if (count($Cont->Conts())) { ?>
		<table class=-styled style="width:100%">
			<tbody id=flexibleContsList>
			<?php foreach ($Cont->Conts() AS $C) { ?>
				<tr itemid="<?=$C?>">
					<td>
					<?php
						$title = util::cutText(util::stripTags($C->title()),40);
						echo $title ?: '<span style="color:#aaa">kein Titel</span>';
					?>
					<td>
					<?php
						echo ' <i>'.D()->module->Entry($C->vs['module'])->Title().'</i>';
					?>
					<td>
					<?php
						echo ' <b>('.$C.')</b>';
					?>
			<?php } ?>
		</table>
		<br>
		<?php if (count($Cont->Conts()) === 1) { ?>
			Möchten Sie diesen "flexiblen Container" durch den einzigen Inhalt ersetzen? <br>
			Es können hier dadurch keine weiteren Inhalte eingefügt werden.
			<br><br>
			<button name="<?=hee($Cont->vs['name'])?>" onclick="
				$fn('page::insertBefore')(<?=$Cont->Parent()?>, <?=$C?>);
				$fn('page::name')(<?=$C?>,this.getAttribute('name'));
				$fn('page::remove')(<?=$Cont?>);
				cms.cont.active = <?=$C?>;
				cms.panel.set('sidebar','settings');
				"
			>Durch Inhalt ersetzen</button>
		<?php } else { ?>
			Verschieben Sie die Inhalte per Drag&amp;Drop
		<?php } ?>

	<?php } ?>
</div>
<script>
$('#flexibleContsList').sortable({
	axis: 'y',
	stop: function(e,data) {
		var id = data.item.attr('itemid');
		var next = data.item.next();
		var nextId = next[0] ? next.attr('itemid') : null;
		$fn('page::insertBefore')(<?=$Cont?>,id,nextId);
		$fn('page::reload')(<?=$Cont?>).run();
	}
});
</script>
<style>
#flexibleContsList td {
	cursor:ns-resize;
}
</style>
