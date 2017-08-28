<?php
namespace qg;

$Mail = D()->mail->Entry($_GET['id']);

if (!$Mail->is()) {
	echo 'does not exist';
	return;
}

if (isset($vars['send'])) $Mail->send();

if (Page($Mail->page_id)->access() < 2) return;

if (isset($_GET['showit'])) {
	while (ob_get_level()) ob_end_clean();
	header('Content-Type: text/html; charset=utf-8');
	$html = $Mail->getHtml();

	/* inline-images */
	preg_match_all("#<img.*?src=['\"]cid:([^'\"]+)#i", $html, $matches);
	$matches = array_unique($matches[1]);
	if ($matches) {
		foreach ($matches as $hash) {
			$filename = D()->one("SELECT path FROM mail_attachment WHERE mail_id = ".$Mail." AND hash = ".D()->quote($hash));
			if (!is_readable($filename)) continue;
			$base64 = 'data:image/' . File::extensionToMime(preg_replace('/.*\.([^.]+$)/', '$1', $filename)) . ';base64,' . base64_encode(file_get_contents($filename));
			$html = str_replace('cid:'.$hash, $base64, $html);
		}
	}
	echo
	'<script>'.
		'alert("Your Browser does not support the sandbox-attribute! Please choose an other Browser"); '.
		'location.href = "about:blank"; '.
		'window.stop();'.
		'document.execCommand("Stop");'.
		'document.write(\'<scr\'+\'ipt>\');'.
		'document.write(\'<!--\');'.
	'</script>'.
	$html;
	exit();
}
?>
<div class=c1-box style="flex-basis:800px">
	<div class=-head>Mail-Details</div>
	<table class=c1-style>
		<tr>
			<td style="width:100px"> Datum
			<td> <?=date('d.m.Y H:i', D()->one("SELECT time FROM log WHERE id = ".$Mail->log_id.""))?>
		<tr>
			<td> Betreff
			<td> <?=hee($Mail->subject)?>
		<tr>
			<td> Absender
			<td> <?=hee($Mail->sender)?>
		<tr>
			<td> Versenden
			<td>
				<?php
				$todo = D()->one("SELECT count(*) FROM mail_recipient WHERE mail_id = ".(int)$_GET['id']." AND sent = 0");
				if ($todo) {
					?><button onclick="confirm('Möchten Sie die E-Mails versenden?') && $fn('page::reload')(<?=$Cont?>,{send:1})">Versenden (<?=$todo?>)</button> <span></span><?php
					//echo ' <button>Test an mich</button>';
				} else {
					echo 'alle Versendet!';
				}
				?>
		<!-- tr>
			<td> Text
			<td> <pre><?=hee($Mail->getText())?></pre -->
		<tr>
			<td colspan=2 style="xpadding:14px 0 0 0">
				<iframe sandbox src="<?=hee(Url()->addParam('showit',1))?>" style="border:none; background:#fff; width:100%; height:500px; padding:10px; box-sizing:border-box; box-shadow:0 0 8px rgba(0,0,0,.4); display:block"></iframe>
	</table>
</div>

<?php
$files = D()->all("SELECT * FROM mail_attachment WHERE mail_id = '".(int)$_GET['id']."' ");
if ($files) {
?>
<div class=c1-box>
	<div class=-head>Anhänge</div>
	<div class=-body>
		<?php foreach ($files as $row) { ?>
			<a
				target=_blank
				style="
					display:inline-block;
					background-position:50%;
					background-size:cover; <?php /*=image::able($File->path)?'background-image:url('.$File->url().'/w-200/img.jpg)':'' */ ?>;
					margin:3px;
					box-shadow:0 0 3px black;
					"
				href="<?=hee(path2uri($row['path']))?>">
				<span style="display:block; margin-top:40px; padding:8px; background:rgba(255,255,255,.95);">
					<?=$row['name']?>
				</span>
			</a>
		<?php } ?>
	</div>
</div>
<?php } ?>

<div class=c1-box>
	<?php
	$sql =
	" SELECT *, r.email as email				" .
	" FROM mail_recipient r						" .
	"	LEFT JOIN usr u ON r.email = u.email	" .
	" WHERE 									" .
	"	r.mail_id = '".(int)$_GET['id']."'		" .
	"";
	?>
	<table class=c1-style style="vertical-align:top">
		<thead>
			<tr class=c1-box-head>
				<th> Empfänger
				<th> Versendet
				<th> Geöffnet
				<th> Error
				<th> Daten
		<tbody>
			<?php foreach (D()->query($sql) as $vs) { ?>
				<tr>
					<td> <?=hee($vs['email'])?>
					<td> <?=$vs['sent']   ? date('d.m.Y H:i', $vs['sent'])   : '-'?>
					<td> <?=$vs['opened'] ? date('d.m.Y H:i', $vs['opened']) : '-'?>
					<td> <?=$vs['error']?>
					<td>
						<div onmouseover="this.style.maxHeight = '400px'" onmouseout="this.style.maxHeight='12px'" style="overflow:auto; max-height:12px; font-size:10px; transition:all .1s">
							<?php
							foreach (unserialize($vs['data']) as $n => $v) {
								echo hee($n.' : '.$v).'<br>';
							}
							?>
						</div>
			<?php } ?>
	</table>
</div>

<?php if (D()->mail1_track) { ?>
<div class=c1-box>
	<div class=-head>Tracking</div>
	<?php
	$sql =
	" SELECT                                ".
	"	t.*, count(t.id) as num             " .
	" FROM                                  ".
	"  mail1_track t			            " .
	"  INNER JOIN mail_recipient r ON t.track_id = r.mail1_track_id " .
	" WHERE 								" .
	"	r.mail_id = '".(int)$_GET['id']."'	" .
	" GROUP BY 								" .
	"	t.url		" .
	"";
	?>
	<table class=c1-style>
		<thead>
			<tr>
				<th> Link
				<th> Aufrufe
		<tbody>
			<?php foreach (D()->query($sql) as $vs) { ?>
				<tr>
					<td> <a href="<?=hee($vs['url'])?>"><?=hee($vs['url'])?></a>
					<td> <?=$vs['num']?>
			<?php } ?>
	</table>
</div>
<?php } ?>
