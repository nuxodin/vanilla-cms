<?php namespace qg; ?>
<div class=c1-box>
	<div class=-head>Einstellungen</div>
	<div class=-body>
		<?php
		$form = new SettingsEditor(G()->SET);
		echo $form->show();
		?>
	</div>
</div>
