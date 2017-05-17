<?php
/* Copyright (c) 2016 Tobias Buschor https://goo.gl/gl0mbf | MIT License https://goo.gl/HgajeK */
namespace qg;

$allow_login_as = $Cont->SET['allow_login_as']->setType('bool')->v || Usr()->superuser;

?>
<div class=beBoxCont>
	<div class=beBox style="flex:0 1 auto">
		<div class=-head>Benutzer hinzufügen</div>
		<?php
		if (isset($_POST['add']) && $_POST['qgToken'] === qg::token()) {
			$exists = D()->one("SELECT id FROM usr WHERE email = ".D()->quote($_POST['email']));
			if ($exists) {
				echo '<div class=-body>Die E-Mail-Adresse existiert bereits!</div>';
			} else {
				D()->usr->insert([
					'log_id'    => liveLog::$id,
					'active'    => 1,
					'email'     => $_POST['email'] ?: null,
					'pw'        => auth::pw_hash($_POST['pw']),
					'firstname' => $_POST['firstname'],
					'lastname'  => $_POST['lastname'],
				]);
			}
		}
		?>
		<form method=post>
			<input hidden name=fake1>
			<input hidden name=fake2 type=password>
			<input type=hidden name=qgToken value="<?=qg::token()?>">
			<table class=c1-style>
				<tr>
					<th> Email:
					<td> <input type=text name=email>
				<tr>
					<th> Passwort:
					<td> <input type=password name=pw autocomplete=new-password>
				<tr>
					<th> Vorname:
					<td> <input type=text name=firstname>
				<tr>
					<th> Nachname:
					<td> <input type=text name=lastname>
				<tr>
					<th>
					<td> <button name=add>hinzufügen</button>
			</table>
		</form>
	</div>

	<div class=beBox style="flex:1">
		<div class=-head> Benutzer suchen </div>
		<div class=-body>
			<input type=search placeholder="suchen..." id=usrSearch style="width:300px; max-width:100%">
		</div>
		<table class=c1-style>
			<thead>
				<tr>
					<th> ID
					<th> Name
					<th> Email
					<th> Firma
					<th> Active
					<th> Sessions
					<th> zuletzt online
					<?php if ($allow_login_as) { ?>
						<th width=20>
					<?php } ?>
					<th width=20>
					<th width=20>
			<tbody data-part=list>
				<?php include 'parts/list.php' ?>
		</table>
	</div>
</div>
