<?php namespace qg ?>
<div>
	<?php
	$T = $Cont->Text('main');
	!trim($T) && $T->get('de')->set(
	'<h1>Fehler 404</h1>
	<h2>Die gewünschte Seite konnte nicht gefunden werden.</h2>
	<br>Eventuell möchten Sie zu einer der folgenden Seiten:'
	);
	?>
	<div<?=$Cont->edit? ' contenteditable cmstxt='.$T->id : ''?>><?=$T?></div>
	<ul>
		<li><?=cms_link(2)?>
	<?php
	appRequestUri;
	$match = "MATCH (t.text) AGAINST (".D()->quote(appRequestUri).")";
	$sql =
	" SELECT p.*, ".$match." as _relevance ".
	" FROM page p INNER JOIN text t on p.title_id = t.id ".
	" WHERE ".
	" p.searchable AND ".
	" (".$match." OR t.text LIKE ".D()->quote("%".appRequestUri."%")." ) ".
	" GROUP BY p.id ";
	" ORDER BY _relevance ";
	" LIMIT 100 ";
	$i = 0;
	if (appRequestUri) foreach (D()->query($sql) as $row) {
		$P = Page($row['id'], $row);
		if (!$P->access()) continue;
//		if (!$P->Page->vs['searchable']) continue;
		if (++$i >= 4) break;
		echo '<li>'.cms_link($P);
	}
	?>
	</ul>
<?php

D()->query("INSERT INTO cms_cont_notfound SET request = ".D()->quote((string)Url()).", log_id = ".liveLog::$id);


if ($Cont->edit) {
	echo '<div class="qgCMS c1-box" style="border:1px solid rgba(0,0,0,.5); background:#fff; margin:10px auto">';

		//html::addJSFile(sysURL.'cms/pub/js/frontend.js');

		if (isset($_POST['setRedirect'])) {
			$sql = "INSERT INTO page_redirect SET ".
						" request  = ".D()->quote(appRequestUri).", ".
						" redirect = ".D()->quote($_POST['redirect'])." ";
			D()->query($sql);
		}
		?>
			<div class=-head>Admin: Direkt-Link definieren nach...</div>
			<form class=-body method=post style="display:flex; margin:0">
				<input type=qgcms-page name=redirect style="width:100%; box-sizing:border-box; border-right:0" />
				<button name=setRedirect><?=L('ok')?></button>
			</form>
		<?php
		if (isset($_GET['delete'])) {
			D()->query("DELETE FROM cms_cont_notfound WHERE 1");
		}
		echo '<div class=-head>fehlgeschlagene Anfragen</div>';
		echo '<div class=-body>';
			echo '<a href="'.Url()->addParam('delete', 1).'">alle Löschen</a><br><br>';
			echo '<table style="width:100%">';
				echo '<tr><th>Request <th style="text-align:right">Anzahl';
			$sql =
			" SELECT count(t.log_id) as num, max(t.log_id) as last_log, t.request " .
			" FROM cms_cont_notfound t 			" .
			" GROUP BY request 					" .
			" ORDER BY num DESC, last_log DESC 	" .
			" LIMIT 1000	";
			foreach (D()->query($sql) as $vs) {
				echo '<tr>';
					echo '<td>';
						$xss = strpos($vs['request'],'javascript') !== false || strpos($vs['request'],'script') !== false;
						if ($xss) {
							echo '<span style="color:red">xss?</span> ';
						} else {
							echo '<a href="'.hee($vs['request']).'"> ';
						}
						echo hee($vs['request']);
						if (!$xss) {
							echo '</a>';
						}
					echo '<td style="text-align:right">';
						echo $vs['num'];
			}
			echo '</table>';
		echo '</div>';
	echo '</div>';
}
?>
</div>
