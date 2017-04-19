<?php namespace qg ?>
<div class=qgCmsFront1DiversManager pid=<?=$Cont?>>
	<label>
		<input class=-visible <?=(int)$Cont->vs['visible']?'checked':''?> type=checkbox>
		<?=L('Sichtbar in der Navigation')?>
	</label><br>
	<br>

	<label>
		<input class=-searchable <?=(int)$Cont->vs['searchable']?'checked':''?> type=checkbox>
		<?=L('Durchsuchbar')?>
	</label><br>
	<br>

	<?php
	$S = G()->SET['cms']['models'];
	$all = [];
	foreach (explode(',',$S->v) as $v) $all[trim($v)] = trim($v);
	$has = isset($all[$Cont->id]);
	$all[$Cont->id] = $has ? null : $Cont->id;
	?>
	<label>
		<input class=-model value="<?=hee(implode(',',$all))?>" name=<?=$S->i?> type=checkbox <?=$has?'checked':''?>>
		<?=L('Als Vorlage unter "Module" anzeigen')?>
	</label><br>
	<br>

	<?=L('Bezeichner')?> (<?=L('Layout-Position')?>):<br>
	<input class=-name value="<?=hee($Cont->vs['name'])?>" style="width:250px"><br>
	<br>


	<?=L('Basis')?>:<br>
	<input class=-basis type=qgcms-page value="<?=hee($Cont->vs['basis'])?>" style="width:250px"><br>
	<br>

	<?=L('Unterseiten-Definition')?>
	<textarea class=-childXML style="display:block; width:100%; height:120px" placeholder="<?=L('Beispiel:')?>

	&lt;page module=&quot;cms.layout.default&quot; visible=&quot;0&quot; online_end=&quot;0&quot;&gt;
	&nbsp; &lt;cont name=&quot;main&quot; &gt;
	&nbsp; &nbsp; &lt;cont module=&quot;cms.cont.text&quot; /&gt;
	&nbsp; &nbsp; &lt;cont module=&quot;cms.cont.table1&quot; /&gt;
	&nbsp; &lt;/cont&gt;
	&lt;/page&gt;
	" rows=4 cols=70><?=$Cont->SET->has('childXML') ? $Cont->SET['childXML']->v : ''?></textarea>
	<br>
</div>
