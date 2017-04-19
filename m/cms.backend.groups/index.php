<?php namespace qg; // zzz ?>
<div class=beBoxCont>
	<?php
	if (isset($_GET['id'])) {
		include $Cont->modPath.'inc.edit.php';
	} else {
		include $Cont->modPath.'inc.overview.php';
	}
	?>
</div>
