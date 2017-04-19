<?php
namespace qg;

if (is_file($Cont->modPath.'options.php')) {
	$return = include $Cont->modPath.'options.php';
	if ($return !== false) return;
}
?>

<div class=qgCmsSettingsEditor pid=<?=$Cont?>>
	<?php
	$Editor = new SettingsEditor($Cont->SET);
	echo $Editor->show();
	?>
</div>
