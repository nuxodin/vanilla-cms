<?php namespace qg ?>
<?php if ($Cont->access() < 2) return; ?>

<div class=qgCmsSettingsEditor pid=<?=$Cont?>>
	<?php
	$Editor = new SettingsEditor($Cont->SET);
	echo $Editor->show();
	?>
</div>
