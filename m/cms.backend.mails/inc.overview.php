<?php namespace qg; ?>
<div class=c1-box>
	<?php
	$sql = 	" SELECT 									" .
			"	m.*, 									" .
			"	count(DISTINCT mr.email) as num,		" .
			"	sum(if(mr.sent,1,0))   as sent,         " .
			"	sum(if(mr.opened,1,0)) as opened		" .
			" FROM                                      " .
			"   mail m                                  ".
			"   LEFT JOIN mail_recipient mr ON m.id = mr.mail_id " .
			" WHERE 1                                   " .
			" GROUP BY m.id								" .
			" ORDER BY m.log_id	DESC					" .
			" LIMIT 1000								" .
			"";
	?>
	<table class=c1-style>
		<thead>
			<tr class=c1-box-head>
				<th> Erstellt
				<th> Betreff
				<th> Absender
				<th> Empfänger
				<th> Versendet
				<th> Geöffnet
		<tbody>
		<?php foreach (D()->query($sql) as $vs) { ?>
			<?php
			if (Page($vs['page_id'])->access() < 2) continue;
			$time = D()->one("SELECT time FROM log WHERE id = ".(int)$vs['log_id']."");
			?>
			<tr>
				<td> <a href="<?=Url()->addParam('id',$vs['id'])?>"><?=$time ? date('d.m.Y H:i', $time) : '-'?></a>
				<td> <a href="<?=Url()->addParam('id',$vs['id'])?>"><?=$vs['subject']?></a>
				<td> <?=$vs['sender']?>
				<td> <?=$vs['num']?>
				<td> <?=$vs['sent']   ?: '-'?>
				<td> <?=$vs['opened'] ?: '-'?>
			<?php } ?>
	</table>
</div>
