<?php
namespace qg;

$t = $vars['table'];
$T = D()->$t;
$SET_T = $Cont->SET['table'][(string)$T];
?>

<div class=-body>
	Suche: <input onkeyup="searchRes(this.value)">
	<script>
		searchRes = function(v) {
			dbRes(<?=$Cont?>,{table:'<?=$T?>',search:v});
		}.c1Debounce(500);
	</script>
</div>
<div data-part=resTable>
	<?php include $Cont->modPath.'parts/resTable.php'; ?>
</div>
