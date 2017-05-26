<?php
namespace qg;

if (isset($vars['deleteAll'])) {
	foreach (D()->Tables() as $Table) {
		if (strpos($Table->name(), '_vers_') !== 0) continue;
		D()->removeTable($Table);
		D()->query("DROP VIEW IF EXISTS ".$Table);
	}
	D()->query("DELETE FROM vers_space");
	echo '<script>location.href = location.href</script>';
	D()->query("DELETE FROM vers_cms_page_changed WHERE space != 0");
	return;
}
if (isset($vars['deleteSpace'])) {
	foreach (D()->Tables() as $Table) {
		if (strpos($Table->name(), '_vers_') !== 0) continue;
		@D()->query("DELETE FROM ".$Table->name().' WHERE _vers_space = '.$vars['deleteSpace']);
	}
	D()->query("DELETE FROM vers_space WHERE space = ".$vars['deleteSpace']);
	D()->query("DELETE FROM vers_cms_page_changed WHERE space = ".$vars['deleteSpace']);
}
$spaces[0] = 0;
foreach (D()->col("SELECT space FROM vers_space") as $space) {
	$spaces[$space] = $space;
}
?>
<div>
	<div class=beBoxCont>
		<div class=beBox style="flex:0 1 auto">
			<div class=-head>Tools</div>
			<div class=-body>
				<button onclick="$fn('page::reload')(<?=$Cont?>,{deleteAll:1})">history alle workspaces löschen</button><br>
				Ungebrauchte Fremdschlüssel der Tabellen <br><b>page_access_grp</b> und <b>page_access_usr</b> <br>müssen von Hand gelöscht werden!!
			</div>
		</div>

		<?php foreach ($spaces as $space) { ?>
			<div class=beBox style="flex:0 1 auto">
				<div class=-head>
					<?=$space==0?'Live':'Space '.$space?>
					<?php if ($space!=0) { ?><button onclick="$fn('page::reload')(<?=$Cont?>,{deleteSpace:<?=$space?>})">löschen</button><?php } ?>
				</div>
				<table class=c1-style>
					<thead>
						<tr>
							<th> Tabelle
							<th> Einträge
							<th> Verlauf
					<tbody>
						<?php foreach (vers::$db as $table => $todoFields) {
							?>
							<tr>
								<td> <?=$table?>
								<td> <?php
									echo $space == 0
										? D()->one("SELECT count(*) FROM ".$table." ")
										: D()->one("SELECT count(*) FROM _vers_".$table." WHERE _vers_space = ".$space." AND _vers_log = 0 ")
									?>
								<td> <?=D()->one("SELECT count(*) FROM _vers_".$table." WHERE _vers_space = ".$space." AND _vers_log > 0  ")?>
						<?php } ?>
				</table>
			</div>
		<?php } ?>
	</div>


	<div class=beBoxCont style="flex:100%" id=trees>
		<?php foreach ($spaces as $space) { ?>
			<div class=beBox style="flex:0 1 auto" space=<?=$space?>>
				<div class=-head><?=$space==0?'Live':'Space '.$space?></div>
				<div style="overflow:auto">
					<table class=c1-style>
					<?php
					cms::$_Pages = [];
					vers::$space = cms_vers::$space = $space;
					foreach (Page(1)->Bough() as $C) {
						$level = (count($C->Path())-1);
						echo '<tr itemid='.$C.'>';
							echo '<td style="text-align:right">'.$C;
							echo '<td style="padding-left:'.($level*10).'px">'.hee($C->Title()).' <span style="font-size:.9em; color:#aaa">('.$C->vs['module'].')</span>';
							echo '<td>';
								if ($space == 0) {
									$liveStr = D()->one("SELECT changed FROM vers_cms_page_changed WHERE page_id = ".$C." AND space = 0");
									$live = strtotime($liveStr) - 3;
									$draftStr = D()->one("SELECT changed FROM vers_cms_page_changed WHERE page_id = ".$C." AND space = 1");
									if ($draftStr===false && isset($spaces[1])) {
										echo '<a href=# to_space=1>zu Space 1</a>';
									}
									echo '<td>';
									echo strftime('%x %H:%M', $live);
								} else {
										$liveStr = D()->one("SELECT changed FROM vers_cms_page_changed WHERE page_id = ".$C." AND space = 0");
										$live = strtotime($liveStr) + 3;
										$draftStr = D()->one("SELECT changed FROM vers_cms_page_changed WHERE page_id = ".$C." AND space = ".$space);
										$draft = strtotime($draftStr);
										echo '<span style="color:'.($live > $draft ? '' : 'red').'">'.strftime('%x %H:%M', $draft).'</span>';
									echo '<td>';
										if ($draft > $live) {
											echo '<a href=# class=-publish>veröffentlichen</a>';
										}
								}
								$timeStr = D()->one("SELECT changed FROM vers_cms_page_changed WHERE page_id = ".$C." AND space = ".$space);
								$time = strtotime($timeStr);
								echo '<td>';
								$diff = time() - $time;
								$x = log($diff, 16);
								$x -= .8;
						 		echo '<svg width="14" height="14" viewbox="0 0 100 100" class="chart"><circle r="'.max(0,25*(6-$x)/6).'" cx="50" cy="50" /></svg>';


								//echo '<td>'.D()->one("SELECT changed_page FROM vers_cms_page_changed WHERE page_id = ".$C." AND space = ".$space);
								//echo '<td>'.D()->one("SELECT changed_inside FROM vers_cms_page_changed WHERE page_id = ".$C." AND space = ".$space);

					}
					?>
					</table>
				</div>
			</div>
		<?php } ?>

		<script>
		// hover
		$('#trees').on('mouseover', 'tr', function(){
			$('#trees .-hover').removeClass('-hover');
			$('#trees tr[itemid='+this.getAttribute('itemid')+']').addClass('-hover');
		});
		// publish
		$('#trees').on('click', '.-publish', function(){
			var pid = this.closest('tr').getAttribute('itemid');
			var fromSpace = parseInt(this.closest('[space]').getAttribute('space'));
			if (!confirm('Seite '+pid+' Veröffentlichen?')) return;
			$fn('cms_vers::publishCont')(pid,  {fromSpace, toSpace:0, subPages:false}).run(()=>{
				$fn('page::reload')(<?=$Cont?>);
			});
		});
		$('#trees').on('click', '[to_space]', function(){
			var pid = this.closest('tr').getAttribute('itemid');
			var toSpace   = parseInt(this.getAttribute('to_space'));
			var fromSpace = parseInt(this.closest('[space]').getAttribute('space'));
			if (!confirm('Seite '+pid+' Veröffentlichen?')) return;
			$fn('cms_vers::publishCont')(pid,  {fromSpace, toSpace, subPages:false}).run(()=>{
				$fn('page::reload')(<?=$Cont?>);
			});
		});
		</script>
		<style>
		#trees .-hover { background-color:#ff9; }
		</style>

	</div>
</div>
