<?php
namespace qg;

$hasMany = $hasMany ?? true;
$search  = $param['search'] ?? '';

$sql = 	" SELECT usr.*, a.access " .
		" FROM 																				" .
		"	usr 																			" .
		"	LEFT JOIN page_access_usr a ON usr.id = a.usr_id AND a.page_id = '".$Cont."' 	" .
		" WHERE 1											                                " ;

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
		"		usr.lastname LIKE ".$qSearchLR."			" .
		"		OR usr.firstname LIKE ".$qSearchLR."		" .
		"		OR usr.email LIKE ".$qSearchLR."			" .
		"	)												" .
		" ORDER BY 											" .
		"	usr.firstname  = ".$qSearch." DESC				" .
		"	,usr.lastname  = ".$qSearch." DESC				" .
		"	,usr.email     = ".$qSearch." DESC				" .
		"	,usr.firstname LIKE ".$qSearchR." DESC			" .
		"	,usr.lastname  LIKE ".$qSearchR." DESC			" .
		"	,usr.email     LIKE ".$qSearchR." DESC			" .
		"	,usr.firstname LIKE ".$qSearchLR." DESC			" .
		"	,usr.lastname  LIKE ".$qSearchLR." DESC			" .
		"	,usr.email     LIKE ".$qSearchLR." DESC			" ;
} else {
	$sql .=	" AND NOT ISNULL(a.access) 						" .
			" ORDER BY 										" .
			"	a.access DESC								" ;
}
$sql .=	"	,usr.firstname									" .
		" LIMIT 100											" ;

?>

<table id=cmsUsrAccessTable class=-styled style="width:100%">
	<thead>
		<tr class=-vertical>
			<th style="text-align:left; width:auto"><?=L('Benutzer')?>
			<th><span class=-access-0><?=L('kein Zugriff')?></span>
			<th><span class=-access-1><?=L('sehen')?></span>
			<th><span class=-access-2><?=L('bearbeiten')?></span>
  			<th><span class=-access-3><?=L('administrieren')?></span>
	<tbody>
		<?php foreach (D()->query($sql)as $vs) { ?>
			<tr>
				<td><?=$vs['email']?>
				<td><input <?=!$vs['access']?'checked':''?>   type=radio name=u_<?=$vs['id']?> value=0>
				<td><input <?=$vs['access']==1?'checked':''?> type=radio name=u_<?=$vs['id']?> value=1>
				<td><input <?=$vs['access']==2?'checked':''?> type=radio name=u_<?=$vs['id']?> value=2>
				<td><input <?=$vs['access']==3?'checked':''?> type=radio name=u_<?=$vs['id']?> value=3>
		<?php } ?>
</table>

<style>
#cmsUsrAccessTable input {
	display:block;
	margin:auto;
}
</style>
