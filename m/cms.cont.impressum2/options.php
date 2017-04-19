<?php namespace qg; ?>

<div class=qgCmsSettingsEditor pid=<?=$Cont?>>
	<?php
	$Editor = new SettingsEditor($Cont->SET);
	echo $Editor->show();
	?>
</div>


<?php if (Usr()->superuser) { ?>
	<br><b>(Superuser only)</b><br><br>

    <div class=qgCmsSettingsEditor pid=<?=$Cont?>>
    	<?php
    	$Editor = new SettingsEditor(G()->SET['cms.cont.impressum2'][$Cont->id]);
    	echo $Editor->show();
    	?>
    </div>

<?php } ?>
<br/>
