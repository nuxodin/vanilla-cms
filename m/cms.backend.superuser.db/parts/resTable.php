<?php
namespace qg;

require_once('cms.backend.superuser.db/lib.php');

$t = $vars['table'];
$T = D()->$t;
$SET_T = $Cont->SET['table'][(string)$T];

//$sqlAccess = $T->accessSql();
$sqlAccess = (int)Usr()->superuser;

$where =  "	".$sqlAccess." > 0 ";

if (isset($vars['find'])) {
	$Primaries = $T->getPrimaries();
	if (isset($Primaries[0])) {
		$Field = $Primaries[0];
		$where .= '	AND '.$Field.' = '.$Field->valueToSql($vars['find']);
	}
}
$ShowFs = [];
foreach ($T->Fields() as $F) {
	if (!$SET_T['field'][(string)$F]['show']->v) continue;
	$ShowFs[] = $F;
}
if (isset($vars['search']) && $vars['search']) {
	$h = util::sqlSearchHelper($vars['search'], $ShowFs);
	$where .= ' AND '.$h['where'];
}


$perPage = 10;
$num = D()->one("SELECT count(*) FROM ".$T." WHERE ".$where);

$numPages = ceil($num / $perPage);

$page = (int)($vars['page'] ?? 1);
$page = min($numPages, $page);
$page = max(1, $page);

$sql =
" SELECT *, 								   " .
"    ".$sqlAccess." as _access				   " .
" FROM 										   " .
"    ".$T." 								   " .
" WHERE										   " .
"    ".$where."  							   " .
( isset($h['order']) ?
" ORDER BY ".$h['order']."					   ":'') .
" LIMIT ".(($page-1)*$perPage).", ".$perPage." " .
"";

$res = D()->query($sql);
?>


<?php if (isset($vars['find']) && is_array($vars['find'])) foreach ($vars['find'] as $name=>$value) { ?>
	<?=$name?>=<?=$value?><br>
<?php } ?>

<div class="pager">
	<?php for ($i=1; $i<=$numPages; $i++) { ?>
		<?php
		if (abs($i-$page) >= 3 && $i >= 3 && $i <= $numPages-2) {
			if ($i < 5 || $i > $numPages-5) { echo '.'; }
			continue;
		}
		?>
		<a onclick="dbRes(<?=$Cont?>,{page:<?=$i?>}); return false;" class="<?=$i==$page?'active':''?>" href="#">
			<?=$i?>
		</a>
		<?=$i==$numPages?'':'|'?>
	<?php } ?>
</div>

<table qgDbTable="<?=hee($T)?>" class="c1-style">
	<thead>
		<tr>
			<?php foreach ($ShowFs as $F) { ?>
				<td qgDbField="<?=hee($F)?>">
					<?=$F?>
			<?php } ?>
			<?php foreach ($T->Children() as $C) { ?>
				<?php $SET_F = $SET_T['field'][$C->Table.'-'.$C]; ?>
				<?php if (!$SET_F['show']->v) continue; ?>
				<td>
			<?php } ?>
			<td>
	<tbody>
		<?php foreach ($res as $vs) { ?>
			<tr qgDbEntry="<?=$T->Entry($vs)?>">
				<?php foreach ($ShowFs as $F) { ?>
					<td><?php cmsBackendSuperuserDbField($Cont, $T->Entry($vs), $F); ?>
				<?php } ?>
				<?php foreach ($T->Children() as $C) { ?>
					<?php $SET_F = $SET_T['field'][$C->Table.'-'.$C]; ?>
					<?php if (!$SET_F['show']->v) continue; ?>
					<td>
						<?php
						$num = D()->one("SELECT count(*) FROM ".$C->Table." WHERE ".$C." = ".$vs['id']." ")
						?>
						<xa href="javascript:$fn('page::loadPart')(<?=$Cont?>,'tEntries',{table:'<?=$C->Table?>',find:{<?=$C?>:'<?=$vs['id']?>'}}).run()">
						<a href="javascript:dbRes(<?=$Cont?>,{table:'<?=$C->Table?>',find:{<?=$C?>:'<?=$vs['id']?>'}});">
							<?=$C->Table?> (<?=$num?>)
						</a>
				<?php } ?>
				<td class=rem onclick="var el = this; $fn('superuser_db::removeEntry')('<?=$T?>','<?=$T->Entry($vs)?>').run(function(done) { done && el.parentNode.remove(); })">
					x
			<?php } ?>
</table>
