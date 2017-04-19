<?php
namespace qg;

$hasMany = $hasMany ?? true;
$search  = $param['search'] ?? '';

$sql = 	" SELECT grp.*, a.access " .
		" FROM 																				" .
		"	grp 																			" .
		"	LEFT JOIN page_access_grp a ON grp.id = a.grp_id AND a.page_id = '".$Cont."' 	" .
		" WHERE page_access											                        " ;

if (!$hasMany) {
	$sql .=	" " .
			" ORDER BY 										" .
			"	a.access DESC								" ;
} else if ($search) {
	$qSearch = D()->quote($search);
	$qSearchR = D()->quote($search.'%');
	$qSearchLR = D()->quote('%'.$search.'%');
	$sql .=
	    "	AND(											" .
		"		grp.name LIKE ".$qSearchLR."				" .
		"	)												" .
		" ORDER BY 											" .
		"	 grp.name  = ".$qSearch." DESC					" .
		"	,grp.name LIKE ".$qSearchR." DESC				" .
		"	,grp.name LIKE ".$qSearchLR." DESC				" ;
} else {
	$sql .=	" AND NOT ISNULL(a.access) 						" .
			" ORDER BY 										" .
			"	a.access DESC								" ;
}
$sql .=	"	,grp.name									    " .
		" LIMIT 100											" ;
?>

<table id=cmsGrpAccessTable class=-styled style="width:100%">
	<thead>
		<tr class=-vertical>
			<th style="text-align:left; width:auto"><?=L('Gruppe')?>
			<th><span class=-access-0><?=L('kein Zugriff')?></span>
			<th><span class=-access-1><?=L('sehen')?></span>
			<th><span class=-access-2><?=L('bearbeiten')?></span>
			<th><span class=-access-3><?=L('administrieren')?></span>
	<tbody>
		<tr>
			<td><?=L('Öffentlich')?>
			<td><input type=radio name=public value=0 <?=$Cont->vs['access']?'':'checked'?>>
			<td><input type=radio name=public value=1 <?=$Cont->vs['access']?'checked':''?>>
			<td>
			<td>
		<?php foreach (D()->query($sql)as $vs) { ?>
			<tr>
				<td><?=$vs['name']?>
				<td><input <?=!$vs['access']?'checked':''?>   type=radio name=g_<?=$vs['id']?> value=0>
				<td><input <?=$vs['access']==1?'checked':''?> type=radio name=g_<?=$vs['id']?> value=1>
				<td><input <?=$vs['access']==2?'checked':''?> type=radio name=g_<?=$vs['id']?> value=2>
				<td><input <?=$vs['access']==3?'checked':''?> type=radio name=g_<?=$vs['id']?> value=3>
		<?php } ?>
</table>

<style>
#cmsGrpAccessTable input {
	display:block;
	margin:auto;
}
</style>
