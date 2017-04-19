<?php
namespace qg;

require_once('../../appinit.php');

if (isset($_POST['edit'])) {
	D()->query(
		" UPDATE grp SET 							" .
		"	name 		= ".D()->quote($_POST['name'])."		" .
		" WHERE id='".(int)$_GET['id']."'	 ");
}
$vs = D()->row("SELECT * FROM grp WHERE id='".(int)$_GET['id']."'");

?>

<h1>Benutzer</h1>
<div class="be_contentTextBox">
	<form method="post">
		<table class="data">
			<tr>
				<td>Name:
				<td><input name=name value="<?=hee($vs['name'])?>" style="width:300px">
		</table>
		<br>
		<br>
		<button name=edit><?=L('Speichern')?></button>
	</form>
</div>
