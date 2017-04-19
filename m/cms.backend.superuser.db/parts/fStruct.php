<?php
namespace qg;

$t = $vars['table'];
$f = $vars['field'];
$T = D()->$t;
if (!$T) return;
$F = $T->$f;
if (!$F) return;

$SET_T = $Cont->SET['table'][$t];

$PTs = [];
foreach (D()->Tables() as $PT) {
	if (count($PT->getPrimaries()) > 1) continue;
	$PTs[$PT->name()] = $PT;
}

?>
<div class=-head>Feld: <?=$F?></div>
<div class=-body>
	<fieldset>
		<legend>Verknüpfung</legend>

		<select onchange="$fn('dbField::setParent')('<?=$T?>','<?=$F?>',$(this).val() ).run(); $(this).next('div')[0].style.display=$(this).val()?'block':'none'">
			<option>
			<?=helper_optionsFromArray($PTs,$F->Parent())?>
		</select><br>
		<div style="display:<?=$F->Parent()?'block':'none'?>">
			<br>
			Was passiert beim löschen des Eintrags in der Bezugstabelle?<br>
			<select onchange="$fn('superuser_db::fieldSetOnParentDelete')('<?=$T?>','<?=$F?>',$(this).val() ).run()">
				<option value="">nichts
				<option value=setnull <?=$F->vs['on_parent_delete']=='setnull'?'selected':''?>>Das Feld "<?=$f?>" auf 0 setzen
				<option value=cascade <?=$F->vs['on_parent_delete']=='cascade'?'selected':''?>>"<?=$t?>"-Eintrag löschen
			</select><br>
		</div>
		<br>
	</fieldset>
	<br>
	<fieldset>
		<legend>Typ</legend>
		<select onchange="$fn('superuser_db::fieldSetTyp')('<?=$T?>','<?=$F?>',$(this).val()).run()">
			<?=helper_optionsFromArray(['VARCHAR','TINYINT','TEXT','DATE','SMALLINT','MEDIUMINT','INT','BIGINT','FLOAT','DOUBLE','DECIMAL','DATETIME','TIMESTAMP','TIME','YEAR','CHAR','TINYBLOB','TINYTEXT','BLOB','MEDIUMBLOB','MEDIUMTEXT','LONGBLOB','LONGTEXT','BOOL','BINARY'], strtoupper($F->getType()), 0)?>
		</select>
		<input onchange="$fn('superuser_db::fieldSetLength')('<?=$T?>','<?=$F?>',$(this).val()).run()" value="<?=$F->getLength()?>" /><br>
		<br>
		<select onchange="$fn('superuser_db::fieldSetSpecial')('<?=$T?>','<?=$F?>',$(this).val()).run()">
			<?=helper_optionsFromArray([' ','UNSIGNED','UNSIGNED ZEROFILL','ON UPDATE CURRENT_TIMESTAMP'], strtoupper($F->getSpecial()), 0)?>
		</select><br>
		<input <?=$F->vs['Null']=='YES'?'checked':''?> type=checkbox onclick="$fn('superuser_db::fieldSetNull')('<?=$T?>','<?=$F?>',this.checked).run()" />
		Null?
		<br>
		<input <?=$F->isAutoincrement()?'checked':''?> type=checkbox onclick="$fn('superuser_db::fieldSetAutoincrement')('<?=$T?>','<?=$F?>',this.checked).run(); $(this).next('input').attr('disabled',this.checked)" />
		Autoincrement?
		<br>
		Default
		<input onchange="$fn('superuser_db::fieldSetDefault')('<?=$T?>','<?=$F?>',$(this).val() ).run()" value="<?=$F->vs['Default']?>" <?=$F->isAutoIncrement()?'disabled="disabled"':''?> /><br>
		<br>
		<input <?=!$F->getkey()?'checked':''?> type=radio onclick="$fn('superuser_db::fieldSetKey')('<?=$T?>','<?=$F?>',0).run()" name="index" />
		Kein Index
		<br>
		<input <?=$F->getKey()=='MUL'?'checked':''?> type=radio onclick="$fn('superuser_db::fieldSetKey')('<?=$T?>','<?=$F?>','MUL').run()" name="index" />
		Index
		<br>
		<input <?=$F->getKey()=='PRI'?'checked':''?> type=radio onclick="$fn('superuser_db::fieldSetKey')('<?=$T?>','<?=$F?>','PRI').run()" name="index" />
		Primary
		<br>
		<input <?=$F->getKey()==='UNI'?'checked':''?> type=radio onclick="$fn('superuser_db::fieldSetKey')('<?=$T?>','<?=$F?>','UNI').run()" name="index" />
		Unique
	</fieldset>
</div>
