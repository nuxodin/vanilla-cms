<?php
namespace qg;

if (isset($_GET['delete']))
	D()->grp->delete((int)$_GET['delete']);

if (isset($_POST['add'])) {
	$id = D()->grp->insert(['name'=>$_POST['name'], 'page_access'=>(int)($_POST['page_access']??0)]);
	header('Location: '.Url());
	exit;
}
$res = D()->query(
	" SELECT grp.*, count(usr_grp.usr_id) as num_usr 		" .
	" FROM grp 												" .
	"	LEFT JOIN usr_grp ON grp.id = usr_grp.grp_id		" .
	" GROUP BY grp.id										" .
	" ORDER BY type, name									");
?>

<div class=c1-box style="flex:0 0 auto">
	<div class=-head>Gruppe erstellen</div>
	<form method=post>
		<table class=c1-style>
			<tr>
				<th> Name:
				<td> <input type=text name=name>
			<tr>
				<th> Relevant für Seiten-Zugriff:
				<td> <input name=page_access value=1 type=checkbox>
			<tr>
				<th>
				<td> <button name=add><?=L('hinzufügen')?></button>
		</table>
	</form>
</div>

<div class=c1-box style="flex:0 0 auto">
	<table class=c1-style>
		<thead>
			<tr class=c1-box-head>
				<th> Id
				<th> Gruppe
				<th> Type
				<th> Relevant für Seiten-Zugriff
				<th width=150> Anzahl Mitglieder
				<th width=16>
				<th width=16>
		<tbody>
		<?php foreach ($res as $vs) { ?>
			<tr data-c1-href="<?=hee(Url()->addParam('id',$vs['id']))?>">
				<td> <?=$vs['id']?>
				<td> <?=hee($vs['name'])?>
				<td> <?=hee($vs['type'])?>
				<td> <?=$vs['page_access']?'yes':'no'?>
				<td style="text-align:right">
					<a href="<?=hee(Url(cms::PageByModule('cms.backend.users')->Page->url())->addParam('grp_id',$vs['id']))?>">
						<?=$vs['num_usr']?>
					</a>
				<td align="right">
					<a href="<?=hee(Url()->addParam('id',$vs['id']))?>">
						<img src="<?=sysURL?>cms.frontend.1/pub/img/pencil.svg" alt="Bearbeiten">
					</a>
				<td align="right">
					<a href="<?=hee(Url()->addParam('delete',$vs['id']))?>" onclick="return confirm('Möchten Sie die Gruppe wirklich löschen?')">
						<img src="<?=sysURL?>cms.frontend.1/pub/img/delete.svg" alt="löschen">
					</a>
		<?php } ?>
	</table>
</div>
