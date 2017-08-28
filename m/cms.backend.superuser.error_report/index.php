<?php namespace qg ?>
<div class=beBoxCont>
	<div class=c1-box style="overflow:auto; width:auto; flex:0 0 auto">
		<div class=-head>Tools</div>
		<div class=-body>
			<?php if (!isset($_GET['latest'])) { ?>
				<a href="<?=hee(Url()->addParam('latest','1'))?>">nach datum sortieren</a><br>
			<?php } else { ?>
				<a href="<?=hee(Url()->stripParam('latest'))?>">nach anzahl sortieren</a><br>
			<?php } ?>
			<br>
			<?php
			if (isset($vars['deleteAll'])) {
				$sql =  "DELETE FROM m_error_report";
				D()->query($sql);
			}
			if (isset($vars['deleteByMessage'])) {
				$sql =  "DELETE FROM m_error_report WHERE message = ".D()->quote($vars['deleteByMessage']);
				D()->query($sql);
			}
			?>
			<button onclick="$fn('page::reload')(<?=$Cont?>,{deleteAll:1}); this.disabled = true">Alle Einträge löschen</button>
		</div>
	</div>

	<?php if (!isset($_GET['message'])) { ?>
		<div class=c1-box style="max-height:88vh; overflow:auto; width:auto;">
			<div class=-head>Errors</div>
			<table class=c1-style>
				<?php
				$rows = D()->all(
					"SELECT *, count(*) as num FROM m_error_report GROUP BY message ORDER BY ".(isset($_GET['latest'])?'id DESC':'count(*) DESC')." "
				);
				?>
				<tbody>
					<?php foreach ($rows as $row) { ?>
						<tr>
							<td>
								<a href="<?=hee(Url()->addParam('message',$row['message']))?>"><?=$row['num']?> x</a>
							<td>
								<?=hee($row['source'])?>
							<td>
								<a target=_blank href="<?=appURL.'editor/?file='.urlencode($row['file']).'&line='.$row['line'].'&col='.$row['col']?>"><?=hee($row['message'])?></a>
							<td>
								<img
									onclick="$fn('page::reload')(<?=$Cont?>,{deleteByMessage:this.getAttribute('value')})"
									value="<?=hee($row['message'])?>"
									src="<?=sysURL?>cms.frontend.1/pub/img/delete.svg"
									class=-rem
									alt="<?=hee(L('Löschen'))?>"
									style="cursor:pointer; height:20px;">
					<?php } ?>
			</table>
			<?php if (!$rows) { ?>
				<div class=-body>Super, bis jetzt keine Fehler!</div>
			<?php } ?>
		</div>
	<?php } else { ?>
		<div class=c1-box style="height:88vh; overflow:auto; width:auto;">
			<div class=-head><?=hee($_GET['message'])?></div>
			<table class=c1-style>
				<?php
				$rows = D()->all(
					"SELECT * FROM m_error_report WHERE message = ".D()->quote($_GET['message'])." ORDER BY time DESC"
				);
				?>
				<tbody>
					<?php foreach ($rows as $row) { ?>
						<tr style="white-space:nowrap">
							<td> <?=$row['time']?> <br> <?=$row['log_id']?>
							<td> <a href="<?=hee(appURL.'editor/?file='.urlencode($row['file']).'&line='.$row['line'].'&col='.$row['col'])?>" target=_blank title="<?=hee($row['file'])?>">goto</a>
							<td> <a href="<?=hee($row['request'])?>" target=_blank>
									<?=$row['request']?>
								 </a><br>
								 <?=$row['browser']?><br>
								 <?=$row['ip']?>
							<td> <pre style="font-size:10px; box-shadow:0 0 5px; padding:4px"><?=hee($row['sample'])?></pre>
							<td>
								<?php
								if (!$row['backtrace']) continue;
								$back = json_decode($row['backtrace'],1);
								if (!$back) continue;
								echo '<table class=c1-padding>';
								foreach ($back as $item) {
									echo '<tr>';
									echo '<td>';

									$fileShow = substr($item['file'],strlen(sysPATH)-2);
									echo '<a href="'.hee(appURL.'editor/?file='.urlencode($item['file']).'&line='.$item['line'].'&col='.$item['col']).'" target=_blank>'.hee($fileShow).'</a>';

									echo '<td>';
									echo hee($item['function']);
									echo '<td>';
									echo hee($item['args']);
								}
								echo '</table>';
								?>
					<?php } ?>
			</table>
		</div>
	<?php } ?>


</div>
