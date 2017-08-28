<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

$vs = D()->row("SELECT * FROM usr WHERE id = ".(int)$_GET['id']);
?>
<div class=beBoxCont itemid="<?=hee($_GET['id'])?>">
	<?php
	?>
	<div class=c1-box style="flex:0 1 auto">
		<div class=-head>Benutzer <?=$vs['id']?></div>
		<div style="overflow:auto">
			<table class="c1-style -detail">
				<tr>
					<th> Active:
					<td>
						<input type=hidden   name=active value=0>
						<input type=checkbox name=active value=1 <?=$vs['active']?'checked':''?>>
				<tr>
					<th> Email:
					<td> <input name=email value="<?=hee($vs['email'])?>">
				<tr>
					<th> Passwort:
					<td> <input name=pw autocomplete=new-password type=password>
				<tr>
					<th> Vorname:
					<td> <input name=firstname value="<?=hee($vs['firstname'])?>">
				<tr>
					<th> Nachname:
					<td> <input name=lastname value="<?=hee($vs['lastname'])?>">
				<tr>
					<th> Firma:
					<td> <input name=company value="<?=hee($vs['company'])?>">
				<?php if (Usr()->superuser) { ?>
				<tr>
					<th> Superuser:
					<td>
						<input type=hidden   name=superuser value=0>
						<input type=checkbox name=superuser value=1 <?=$vs['superuser']?'checked':''?>>
				<?php } ?>
			</table>
		</div>
	</div>

	<div class=c1-box style="flex:0 1 auto">
		<div class=-head>Gruppen</div>
		<table class="c1-style -set_grp" style="width:auto">
		<?php foreach (D()->query("SELECT grp.*, usr_grp.usr_id as has FROM grp LEFT JOIN usr_grp ON grp.id = usr_grp.grp_id AND usr_grp.usr_id = ".(int)$_GET['id']) as $vs) { ?>
			<tr>
				<td><?=$vs['name']?>
				<td>
					<input type=checkbox value=<?=$vs['id']?> <?=$vs['has']?'checked':''?> value=1>
		<?php } ?>
		</table>
	</div>
</form>
