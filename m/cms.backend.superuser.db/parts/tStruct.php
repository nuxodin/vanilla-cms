<?php
namespace qg;

$t = $vars['table'];
$T = D()->$t;
$SET_T = $Cont->SET['table'][$t];
?>

<div class=-head>Tabelle <?=$t?></div>
<div class=-body>
	<a onclick="dbRes(<?=$Cont?>,{table:'<?=$T?>'})" style="display:block;" href="#">
		Einträge: <?=D()->one("SELECT count(*) FROM ".$T."")?>
	</a>

	<h3>Felder <span onclick="dbAddField(<?=$Cont?>,'<?=$t?>')" style="font-size:30px; cursor:pointer; font-weight:normal; vertical-align:middle">+</span></h3>
	<ul>
	<?php foreach ($T->Fields() as $F) {
		if ($F->name() === '_qgtmp') continue;
		?>
		<?php $SET_F = $SET_T['field'][(string)$F]; ?>
		<?php $SET_F['show']->custom(); ?>
		<li>
			<input onclick="setFieldShow(<?=$Cont?>,'<?=$T?>','<?=$F?>',this.checked);" type=checkbox <?=$SET_F['show']->v?'checked':''?>>
			<a href="#" onclick="fieldUi('<?=$T?>','<?=$F?>'); return false" style="<?=$F->isPrimary()?'font-weight:bold':''?>; <?=$F->Parent()?'color:red':''?>">
				<?=$F?>
			</a>
	<?php } ?>
	</ul>

	<h3>Verknüpfte Felder</h3>
	<ul>
	<?php foreach ($T->Children() as $F) { ?>
		<?php $SET_F = $SET_T['field'][$F->Table.'-'.$F]; ?>
		<?php $SET_F['show']->custom(); ?>
		<li>
			<input onclick="setFieldShow(<?=$Cont?>,'<?=$T?>','<?=$F->Table.'-'.$F?>',this.checked);" type=checkbox <?=$SET_F['show']->v?'checked':''?>>
			<a href="javascript:$fn('page::loadPart')(<?=$Cont?>,'fStruct',{table:'<?=$T?>',field:'<?=$F?>'}).run()">
				<?=$F->Table?> | <?=$F?>
			</a>
	<?php } ?>
	</ul>

	<script>
		window.fieldUi = async function(table, field){
			var [dialog] = await c1.c1Use('dialog',1);
			$fn('page::getPart')(<?=$Cont?>,'fStruct',{table,field}).run(function(body){
				var d = new dialog({title: table+' | '+field, body, class:'qgCMS'});
				d.element.c1Find('>.-foot').remove();
				d.show();
			})
		}
	</script>

</div>
