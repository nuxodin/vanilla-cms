<?php
namespace qg;

if ($Cont->edit) {
	html::addJsFile(sysURL.'core/js/qg/tableHandles.js');
	html::addJsFile($Cont->modUrl.'pub/edit.js');
}

if (!$Cont->SET->has('cols')) {
	$Cont->SET['cols'] = 2;
	$Cont->SET['rows'] = 2;
}

$cols = min((int)$Cont->SET['cols']->v, 15);
$rows = min((int)$Cont->SET['rows']->v, 300);
$cols = max($cols, 1);
$rows = max($rows, 1);

$units = $Cont->SET['units']->v === 'px' ? 'px' : '%';

if (isset($_GET['export_table']) AND $_GET['export_table'] == $Cont->id) {
	while (ob_get_level()) ob_end_clean();

	header('Content-Type: application/x-msdownload');
	header('Content-Disposition: inline; filename="'.$Cont->Title().'_'.date("d.m.y").'.xls"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$data = '';
	for ($i=0; $i<$rows; $i++) {
    $r = $Cont->SET['direction']->v ? $rows-$i : $i;
		for ($j=0; $j<$cols; $j++) {
			$data .= "\"".strip_tags($Cont->text($r.'_'.$j))."\"\t";
		}
		$data .= "\"\"\n";
	}
	$data = str_replace("\r","",utf8_decode($data));
	echo $data;
	exit();
}
?>
<div>
	<table>
		<tbody>
		<?php for ($i=0; $i<$rows; $i++) { ?>
		    <?php $r = $Cont->SET['direction']->v ? $rows-1-$i : $i; ?>
			<tr>
				<?php for ($j=0; $j<$cols; $j++) { ?>
					<?php
					$T = $Cont->text($r.'_'.$j);
					$w = $Cont->SET['row_'.($j+1)]->v;
					?>
					<td<?=$w?' style="width:'.$w.$units.'"':''?>>
						<div <?=$Cont->edit?'contenteditable cmstxt='.$T->id:''?>><?=$T?></div>
				<?php } ?>
		<?php } ?>
	</table>
</div>
