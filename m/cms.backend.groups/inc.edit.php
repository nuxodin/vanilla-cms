<?php
namespace qg;

if (isset($_POST['edit'])) {
	D()->grp->update((int)$_GET['id'], $_POST);
	header('Location: '.Url()->stripParam('id'));
	exit();
}
$vs = D()->row("SELECT * FROM grp WHERE id='".(int)$_GET['id']."'");
?>
<form method=post class=beBox style="flex:0 0 auto">
	<div class=-head> Details Gruppe "<?=hee($vs['name'])?>" </div>
	<table class=c1-style>
		<tr>
			<td> Name:
			<td> <input name=name value="<?=hee($vs['name'])?>" style="width:300px">
		<tr>
			<td> Type:
			<td> <input name=type value="<?=hee($vs['type'])?>" style="width:300px">
		<tr>
			<td> Relevant für Seiten-Zugriff:
			<td>
				<input type=hidden name=page_access value=0>
				<input type=checkbox name=page_access value=1 <?=$vs['page_access']?'checked':''?>>
	</table>
	<div class=-body>
		<button name=edit><?=L('Speichern')?></button>
	</div>
</form>
