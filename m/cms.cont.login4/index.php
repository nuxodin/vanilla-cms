<?php namespace qg ?>
<div>
<?php
if (!$Cont->edit && Usr()->is()) {
	$redirect = (int)$Cont->SET['redirect']->v;
	if ($redirect) {
		header('Location: '. Url( Page($redirect)->url() ) );
		exit;
	}
}

$T = $Cont->Text('login failed');
!(string)$T && $T->set('Ihr Loginversuch ist fehlgeschlagen');
if (isset(G()->loginError)) {
	switch (G()->loginError) {
		case 'username':
		case 'inactive':
		case 'password':
			?><div class=loginError><?=$T?></div><?php
	}
}
?>
<?php if (!Usr()->is() || $Cont->edit) { ?>

	<?php $i=0; foreach (Client()->Usrs() as $ClientUsr) {
		if ($Cont->SET['history']->v < ++$i) break;
		$Usr = $ClientUsr->Usr();
		?>
		<form method=post>
				<?php if ($Cont->SET['saveLogin']->v) { ?>
					<input name=save_login    type=checkbox value=1 <?=$ClientUsr->save_login ? 'checked' : ''?>>
				<?php } ?>
				<?=hee($Usr->email)?>
				<input name=email	          type=hidden value="<?=hee($Usr->email)?>">
				<?php if (!$ClientUsr->save_login) { ?>
					<input name=pw          type=password>
				<?php } ?>
				<button name=liveUser_login><?=L('login')?></button>
		</form>
	<?php } ?>

	<form method=post>
		<?php if ($Cont->SET['fix user']->v) { ?>
			<input type=hidden name=email value="<?=$Cont->SET['fix user']->v?>">
		<?php } ?>
		<table class="c1-padding c1-fieldTable">
			<?php if (!$Cont->SET['fix user']->v) { ?>
			<tr class=-email>
				<th>
					<?php $T = $Cont->Text('user'); !(string)$T && $Cont->Text('user','de','E-Mail:'); ?>
					<div <?=$Cont->edit?'contenteditable cmstxt='.$T->id:''?>><?=$T?></div>
				<td>
					<input name=email type=text required <?=$Cont->SET['no autofocus']->v?'':'autofocus'?>>
			<?php } ?>
			<tr class=-pw>
				<th>
					<?php $T = $Cont->Text('pw'); !(string)$T && $Cont->Text('pw','de','Passwort:'); ?>
					<div <?=$Cont->edit?'contenteditable cmstxt='.$T->id:''?>><?=$T?></div>
				<td>
					<input name=pw type=password required>
			<tr  class=-login>
				<th>
				<td>
					<button name=liveUser_login><?=L('Anmelden')?></button>
			<?php if ($Cont->SET['saveLogin']->v) { ?>
				<tr  class=-save_login>
					<th>
						<?php $T = $Cont->Text('saveLogin'); !(string)$T && $Cont->Text('saveLogin','de','Eingeloggt bleiben:'); ?>
						<div <?=$Cont->edit?'contenteditable cmstxt='.$T->id:''?>><?=$T?></div>
					<td>
						<label>
							<input name=save_login type=checkbox value=1 class=c1-fakable><i></i>
						</label>
			<?php } ?>
		</table>
	</form>
	<?php } else {
		$redirect = $Cont->SET['logout_redirect']->v;
		?>
		<form method=post <?=$redirect ? 'action="'.Page($redirect)->url().'"' : ''?> >
			<button name=liveUser_logout><?=L('Abmelden')?></button>
		</form>
	<?php } ?>
</div>
