<?php namespace qg ?>

<div class=qgCmsFront1MoreManager>
	<div class=-standalone>
		<div class=-h1>
			<span><?=L('Angemeldet als:')?> <?=Usr()->firstname?> <?=Usr()->lastname?></span>
			<a href="<?=hee(Url()->addParam('liveUser_logout',1))?>"><button style="margin:0"><?=L('abmelden')?></button></a>
		</div>
	</div>

	<?php
	if (isset($param['msg'])) {
		$arr = [
			'Message:'  => $param['msg'],
			'Link'      => $param['link'],
			'Browser'   => $_SERVER['HTTP_USER_AGENT'],
			'E-Mail:'   => Usr()->email,
			'Firstname' => Usr()->firstname,
			'Lastname'  => Usr()->lastname
		];
		$Mail = D()->mail->Entry();
		$Mail->subject    = 'CMS feedback';
		$Mail->reply_to   = Usr()->email;
		//$Mail->sender     = Usr()->email;
		//$Mail->sendername = Usr()->firstname.' '.Usr()->lastname;
		$Mail->html       = array2formatedStr($arr);
		$Mail->addTo(G()->SET['cms']['feedback']['email']->v);
		$Mail->send();
		G()->SET['cms']['feedback']['text']->setUser('');
		echo '<br><i style="color:#4c4">Danke für Ihr Feedback. <br>Wir werden uns so schnell wie möglich bei Ihnen melden.</i><br>';
	}
	?>
	<div class="-widgetHead -open"><span class=-title><?=L('Feedback / Support')?></span></div>
	<div>
		<form class=-feedbackform>
			<textarea placeholder="<?=L('Nachricht an:')?> <?=G()->SET['cms']['feedback']['email']->v?>" name=msg required style="width:100%; height:200px"><?=G()->SET['cms']['feedback']->make('text','')->custom()->v?></textarea>
			<br>
			<button style="padding:10px 50px; width:100%">senden</button>
		</form>
	</div>

	<div class=-widgetHead><span class=-title><?=L('Passwort ändern')?></span></div>
	<div>
		<form class=-pwchange>
			<table style="width:215px" class=c1-padding>
				<tr>
					<td> <input style="width:100%" placeholder="<?=L('altes Passwort')?>" type=password name=old>
				<tr>
					<td> <input style="width:100%" placeholder="<?=L('neues Passwort')?>" type=password name=new>
				<tr>
					<td> <input style="width:100%" placeholder="<?=L('neues Passwort wiederholen')?>" type=password name=new2>
				<tr>
					<td> <button><?=L('ändern')?></button>
			</table>
		</form>
	</div>

	<div class=-widgetHead><span class=-title><?=L('CMS Einstellungen')?></span></div>
	<div>
		<table class=-styled style="width:100%">
			<tr>
				<td><?=L('Sprache')?>
				<td>
					<?php $S = G()->SET['qg']['lang_ns']['cms']; ?>
					<select class=-changelang name=<?=$S->i?>>
						<?php foreach (D()->smalltext->Fields() as $f => $F) {
							if (strlen($f) > 2) continue; ?>
							<option <?=$S->v === $f?'selected':''?> ><?=$f?>
						<?php } ?>
					</select>
			<tr>
				<td><?=L('Inhalte in der Struktur darstellen?')?>
				<td>
					<?php $S = G()->SET['cms.frontend.1']['custom']['tree_show_c']; ?>
					<input class=-tree-show-c type=checkbox name=<?=$S->i?> <?=$S->v?'checked':''?>>
			<!--tr>
				<td><?=L('Seite durch CMS-Panel verdrängen?')?> (beta)
				<td>
					<input type=checkbox <?=G()->SET['cms.frontend.1']['custom']['crowd out']->v?'checked':''?> onchange="cms.panel.set('crowd out',this.checked)"-->
		</table>
	</div>

	<div class=-widgetHead><span class=-title><?=L('Tastenbefehle')?></span></div>
	<div>
		<table class=-styled style="width:100%">
			<tr>
				<th> e
				<td> Editmodus ein- / ausschalten
			<tr>
				<th> space
				<td> Einstellungen der Seite oder des Inhaltes bei Mouseover
			<tr>
				<th> t
				<td> Seite in der Stuktur anzeigen. Bei Mouseover über einem Inhalt wird dieser in der Struktur angezeigt
			<tr>
				<th> v
				<td> Modul / Inhalt hinzufügen
			<tr>
				<th> b
				<td> Zum Backend / Zurück zur Seite
			<tr>
				<th> n
				<td> Neue Unterseite erstellen
		</table>
	</div>

	<!--div class=-widgetHead><span class=-title><?=L('Bearbeitbare Elemente hervorheben')?></span></div>
	<div style="text-align:center">
		<button class=-showEditables>
			<div style="padding:10px; margin:10px; background: #Faa">Textfelder</div>
			<div style="padding:10px; margin:10px; background: var(--cms-color); !important; box-shadow: -2px -2px 5px 0px rgba(0,0,0,.9);">Inhalte</div>
			<div style="padding:10px; margin:10px; background: var(--cms-color); !important; box-shadow: -2px -2px 5px 0px rgba(0,0,0,.9); outline: 4px solid red; outline-offset: -2px;">Container für Inhalte</div>
		</button>
	</div-->

	<div class=-widgetHead><span class=-title><?=L('About')?></span></div>
	<div>
		<a href="https://vanilla-cms.org/de/home" target=_blank>vanilla-cms.org</a>
		<br>
		Feedback willkommen!
	</div>
</div>
