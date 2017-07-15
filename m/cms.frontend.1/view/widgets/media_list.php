<?php
namespace qg;

if (!count($Cont->FilesAndPlaceholders())) {
	echo L('Es sind keine Dateien vorhanden');
	return;
}
?>
<table class="-cmsFileList -styled">
	<tbody cmsconf=media_list_trs>
		<?php include sysPATH.'cms.frontend.1/view/widgets/media_list_trs.php'; ?>
</table>
<?php if ($Cont->Files()) { ?>
	<div style="text-align:right;">
		<?=count($Cont->Files())?> Files | <a target=_blank href="<?=appURL?>?qgCms_page_files_as_zip=<?=$Cont?>">Download ZIP</a>
	</div>
<?php } ?>
