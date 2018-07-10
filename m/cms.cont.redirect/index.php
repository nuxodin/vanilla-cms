<?php namespace qg;
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */

$temp = $Cont->SET['temporary']->setType('bool')->v;
$js   = $Cont->SET['use js']->setType('bool')->v;
$link = $Cont->Text('_redirect');
$redirect = '';
$Page = null;
if (is_numeric(trim($link))) $Page = Page($link);
else {
	switch ($link) {
		case '__parent__':
			$Page = Page()->Parent(); break;
		case '__first-child__':
			$Tmp = Page()->Children('readable');
			$Page = reset($Tmp); break;
		case '__last-child__':
			$Tmp = Page()->Children('readable');
			$Page = end($Tmp); break;
		default:
			$redirect = trim($link);
	}
}
if ($Page) $redirect = (string)Url($Page->url());

if (!$Cont->edit) {
	if (!$redirect) return;
	if ((string)Url() === $redirect) return; // prevent loop
	$code = $temp ? '302' : '302';
	if ($js) {
		echo '<script>location.href = "'.hee($redirect).'"</script>';
		return;
	} else {
		header('HTTP/1.1 '.$code);
		header('Location: '.$redirect);
		exit;
	}
}
?>
<div class="qgCMS c1-box" style="border:1px solid rgba(0,0,0,.5); background:#fff; max-width:400px; margin:10px auto">
	<div class=-head><?=L('Weiterleitung')?></div>
	<div class=-body>
		<?=L('Interne Seite (Vorschläge) oder URL:')?><br>
		<input value="<?=hee($link)?>" type=qgcms-page onblur="$fn('cms::setTxt')(<?=$link->id?>, $(this)[0].value); $fn('page::reload')(<?=$Cont?>)" style="width:100%" />
		<br>
		oder:<br>
		<select onchange="$fn('cms::setTxt')(<?=$link->id?>, $(this).val()); $fn('page::reload')(<?=$Cont?>).run()" style="width:200%">
			<option value="">
			<option <?=$link=='__parent__'      ? 'selected':''?> value="__parent__"     > Vaterseite
			<option <?=$link=='__first-child__' ? 'selected':''?> value="__first-child__"> Erste Unterseite
			<option <?=$link=='__last-child__'  ? 'selected':''?> value="__last-child__" > Letzte Unterseite
		</select>
		<br>
		<br>
		<div style="font-size:0.9em">
			<div style="color:var(--cms-access-3)"><?=L('Die Weiterleitung ist sprachabhängig!')?> </div>
			<br>
			<div style="color:var(--cms-access-3)"><?=L('Diese Seite wird nur im Edit-Modus angezeigt.')?> </div>
			<?=L('Normal wird direkt an die definierte Adresse weitergeleitet.')?>
		</div>
		<br>
		<a href="<?=hee($redirect)?>">
			<button style="display:block; padding:20px; max-width:100%; width:400px">
				Gehe zu: <?=$Page?$Page->Title():$redirect?>
			</button>
		</a>
	</div>
</div>
