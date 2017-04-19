<?php
namespace qg;

$allow_login_as = $Cont->SET['allow_login_as']->setType('bool')->v || Usr()->superuser;

$search = $vars['search'] ?? '';
$sh = sqlSearchHelper($search, ['lastname', 'firstname', 'company', 'email']);
$res = D()->query(
	"SELECT * 						" .
	"FROM usr 						" .
	"WHERE ".$sh['where']." 		" .
	(isset($_GET['grp_id'])?
	"  AND id IN(SELECT usr_id FROM usr_grp WHERE grp_id = ".(int)$_GET['grp_id'].")" : "").
	"ORDER BY ".$sh['order']." 		" .
	"LIMIT 200						"
);
foreach ($res as $vs) {
	if ($vs['superuser'] && !Usr()->superuser) continue;

	$stat = D()->row(
		" SELECT count(distinct sess.id) as sessions 		" .
		" FROM sess 										" .
		" WHERE usr_id = '".$vs['id']."' GROUP BY usr_id");
	?>
	<tr itemid=<?=$vs['id']?> data-c1-href="<?=hee(Url($Cont->Page->url())->addParam('id',$vs['id']))?>">
		<td> <?=$vs['id']?>
		<td>
			<a href="<?=hee(Url($Cont->Page->url())->addParam('id',$vs['id']))?>">
				<?=hee($vs['firstname'].' '.$vs['lastname'])?>
			</a>
		<td>
			<?php $is = preg_match('/@/',$vs['email']) ?>
			<?=$is?'<a href="mailto:'.$vs['email'].'">':''?>
				<?=hee($vs['email'])?>
			<?=$is?'</a>':''?>
		<td> <?=hee($vs['company'])?>
		<td> <?=$vs['active']?'yes':'no'?>
		<td> <?=hee($stat['sessions'])?>
		<?php if ($allow_login_as) { ?>
			<td class=-loginAs>
				<img src="<?=sysURL?>cms.backend.users/pub/change-user.svg" alt="Login als user">
		<?php } ?>
		<td>
			<a href="<?=hee(Url($Cont->Page->url())->addParam('id',$vs['id']))?>">
				<img src="<?=sysURL?>cms.frontend.1/pub/img/pencil.svg" alt="Bearbeiten">
			</a>
		<td class=-delete>
			<img src="<?=sysURL?>cms.frontend.1/pub/img/delete.svg" alt="Löschen">
	<?php
}
