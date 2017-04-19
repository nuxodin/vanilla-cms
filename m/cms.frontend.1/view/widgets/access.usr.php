<?php
namespace qg;
$hasMany = D()->one('SELECT count(*) FROM usr') > 10;
?>
<div class=qgCmsFront1AccessUsrManager pid=<?=$Cont?>>
	<?php if ($hasMany) { ?>
		<input class=-search placeholder="<?=L('Suchen')?>">
	<?php } ?>
	<div widget="access.usr.list">
		<?php include sysPATH.'cms.frontend.1/view/widgets/access.usr.list.php'; ?>
	</div>
</div>
