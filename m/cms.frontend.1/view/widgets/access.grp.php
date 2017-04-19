<?php namespace qg; ?>
<div class=qgCmsFront1AccessGrpManager pid=<?=$Cont?>>
	<label style="display:block; margin-bottom:10px">
		<input class=-inherit type=checkbox value="<?=(int)$Cont->isPublic()?>" <?=$Cont->vs['access']===null?'checked':''?>>
		<?=L('Gruppen-Berechtigungen vererbt')?>
	</label>

	<?php if ($Cont->vs['access']===null) { ?>
		<?=L('Vererbt von ###1###', cms_link( $Cont->accessInheritParent()) )?>
	<?php } else {
		$hasMany = D()->one('SELECT count(*) FROM grp WHERE page_access') > 10;
		?>
		<?php if ($hasMany) { ?>
			<input class=-search placeholder="<?=L('Suchen')?>">
		<?php } ?>
		<div widget="access.grp.list">
			<?php include sysPATH.'cms.frontend.1/view/widgets/access.grp.list.php'; ?>
		</div>
	<?php } ?>
</div>
