<?php
namespace qg;

function cmsBackendSuperuserDbField($Cont, $E, $F) {
	$f = (string)$F;
	$value = $E->$f;
	if ($F->Parent()) { ?>
		<a onclick="dbRes(<?=$Cont?>,{table:'<?=$F->Parent()?>',find:{<?=$F->Parent()->getAutoIncrement()?>:'<?=hee($value)?>'}}); return false;" href="#">
			<?php
			$col = D()->col("SELECT name FROM qg_db_field WHERE tab='".$F->Parent()."' ORDER BY name");
			foreach ($col as $x) {
				echo $F->Parent()->Entry($value)->$x;
				echo ' ';
			}
			if (!$col) {
				echo hee($value);
			}
			?>
		</a>
	<?php } elseif ($F->isPrimary()) { ?>
		<b><?=$value?></b>
	<?php } else { ?>
		<?php if ($F->getType()=='tinyint' && $F->getLength()==1) { ?>
			<input class=qgDbField type=checkbox <?=$value?'checked':''?> value=1>
		<?php } elseif ($F->getType() === 'text') { ?>
			<textarea class=qgDbField><?=hee($value)?></textarea>
		<?php } else { ?>
			<input class=qgDbField value="<?=hee($value)?>">
		<?php } ?>
	<?php }
}
