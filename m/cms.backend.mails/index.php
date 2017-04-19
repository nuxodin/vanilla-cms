<?php namespace qg; ?>
<div class=beBoxCont>
	<?php
	if (isset($_GET['id'])) {
		include $Cont->modPath.'inc.detail.php';
	} else {
		include $Cont->modPath.'inc.overview.php';
	}
	?>
</div>
