<?php namespace qg; ?>

<div class=qgCmsSettingsEditor pid=<?=$Cont?>>
	<?php
	$Editor = new SettingsEditor($Cont->SET);
	echo $Editor->show();
	?>
</div>
