<?php
namespace qg;

$cols = max(1,$Cont->SET['cols']->v);
$rows = max(1,$Cont->SET['rows']->v);
$percent = floor(100 / $cols);
$divID	= i();
$errorID = i();
?>



<input type=number value="<?=$Cont->SET['rows']?>" min=1 max=300 oninput="$fn('page::setDefault')(<?=$Cont?>,{rows:this.value}).run()" style="width:80px; font-size:18px;">
Zeilen (max: 300)<br>
<br>
<input type=number value="<?=$Cont->SET['cols']?>" min=1 max=15 oninput="$fn('page::setDefault')(<?=$Cont?>,{cols:this.value}); cms.cont(cms.cont.active).showWidget('options')" style="width:80px; font-size:18px;">
Spalten (max: 15)<br>

<br>

<label>
  <input onclick="$fn('page::setDefault')(<?=$Cont?>,{direction:this.checked}).run()" <?=$Cont->SET['direction']->v?'checked':''?> value=1 type=checkbox>
  neue Zeilen am Anfang<br>
</label>

<p>&nbsp;</p>
<h3>Spaltenbreiten in
	<select onchange="$fn('page::setDefault')(<?=$Cont?>,{units:this.value}).run()">
		<option>%
		<option <?=$Cont->SET['units']->v==='px'?'selected':''?> >px
	</select>
</h3>
<br>
<p id="<?=$errorID?>" style="color:#FF0000; display:none;">Bitte beachten Sie: Die Summe aller Spalten muss 100% ergeben.</p>
<div id="<?=$divID?>">
	<?php for ($i=1;$i<=$cols;$i++) { ?>
	<div style="width:<?=$percent?>%; float:left; text-align:center;">
		<?=$i?><br>
		<input type=number value="<?=$Cont->SET['row_'.$i]?>" oninput="$fn('page::setDefault')(<?=$Cont?>,{<?='row_'.$i?>:this.value}).run();" style="width:93%;" />
	</div>
	<?php } ?>
</div>

<p>&nbsp;</p>
<a href="<?=Url()->addParam('export_table', $Cont)?>">Tabelle als Excel exportieren</a>
